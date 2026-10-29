<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\BrandLedger;
use App\Models\BrandPayment;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class BrandLedgerService
{
    /**
     * Recalculate all running balances for a Brand in chronological order.
     */
    public function recalculateBrandBalances(int $brandId): void
    {
        $transactions = BrandLedger::where('brand_id', $brandId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $running = 0.00;
        foreach ($transactions as $tx) {
            // Reversed and reversal transactions must not alter running balance
            if (!$tx->is_reversed && $tx->transaction_type !== 'reversal') {
                $running = $running + (float)$tx->debit - (float)$tx->credit;
            }
            if (abs((float)$tx->running_balance - $running) > 0.0001) {
                $tx->update(['running_balance' => $running]);
            }
        }
    }

    /**
     * Post Brand Job Billing to Brand Ledger on completion/verification.
     * Debit increases SerWizo's receivable from Brand.
     */
    public function postJobBilling(ServiceRequest $serviceRequest, ?int $userId = null): ?BrandLedger
    {
        return $this->syncJobBilling($serviceRequest, $userId);
    }

    /**
     * Synchronize Brand Job Billing whenever a job is verified or edited.
     */
    public function syncJobBilling(ServiceRequest $serviceRequest, ?int $userId = null): ?BrandLedger
    {
        $brandId = $serviceRequest->brand_id ?: $serviceRequest->serviceJob?->brand_id;
        $rate = (float)($serviceRequest->brand_rate ?: 0);

        $isBrandBillingEligible = (bool)(
            $brandId && 
            $rate > 0 && 
            in_array($serviceRequest->status, ['completed', 'verified'])
        );

        $existingTx = BrandLedger::where('service_request_id', $serviceRequest->id)
            ->where('is_reversed', false)
            ->where('transaction_type', '!=', 'reversal')
            ->first();

        // If no longer eligible for brand billing (e.g. rate set to 0 or brand removed), reverse existing
        if (!$isBrandBillingEligible) {
            if ($existingTx) {
                $oldBrandId = $existingTx->brand_id;
                $this->reverseTransaction(
                    $existingTx,
                    "Reversal: Job {$serviceRequest->sr_number} is no longer eligible for Brand billing",
                    $userId ?? 1
                );
                $this->recalculateBrandBalances($oldBrandId);
            }
            if ($serviceRequest->is_brand_ledger_posted) {
                $serviceRequest->update(['is_brand_ledger_posted' => false]);
            }
            return null;
        }

        return DB::transaction(function () use ($serviceRequest, $brandId, $rate, $existingTx, $userId) {
            $brand = Brand::lockForUpdate()->findOrFail($brandId);

            $jobTypeLabel = $serviceRequest->is_free_installation ? 'Installation Billing' : 'Brand After-Sale Billing';
            if ($serviceRequest->job_nature === 'brand_after_sale') {
                $jobTypeLabel = 'Brand After-Sale Service';
            }
            $desc = "{$jobTypeLabel}: {$serviceRequest->sr_number}";
            if ($serviceRequest->model_number) {
                $desc .= " (Model: {$serviceRequest->model_number})";
            }
            if ($serviceRequest->warranty_coverage) {
                $desc .= " [" . ucwords(str_replace('_', ' ', $serviceRequest->warranty_coverage)) . "]";
            }

            $effectiveCreatedAt = $serviceRequest->completed_at ?? $serviceRequest->verified_at ?? now();

            if ($existingTx) {
                $oldBrandId = $existingTx->brand_id;

                $existingTx->update([
                    'brand_id' => $brand->id,
                    'debit' => $rate,
                    'credit' => 0.00,
                    'description' => $desc,
                    'created_at' => $effectiveCreatedAt,
                ]);

                if ($oldBrandId !== $brand->id) {
                    $this->recalculateBrandBalances($oldBrandId);
                }
                $this->recalculateBrandBalances($brand->id);
                $existingTx->refresh();

                if (!$serviceRequest->is_brand_ledger_posted) {
                    $serviceRequest->update(['is_brand_ledger_posted' => true]);
                }

                AuditLogService::log(
                    action: 'updated',
                    module: 'brand_ledger',
                    recordId: $existingTx->id,
                    description: "Updated brand billing to Rs. {$rate} for {$brand->name} ({$serviceRequest->sr_number})",
                    userId: $userId
                );

                return $existingTx;
            }

            // Create new transaction
            $tx = BrandLedger::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'brand_id' => $brand->id,
                'service_request_id' => $serviceRequest->id,
                'transaction_type' => 'job_billing',
                'debit' => $rate,
                'credit' => 0.00,
                'running_balance' => 0.00,
                'description' => $desc,
                'reference_number' => $serviceRequest->sr_number,
                'created_by_user_id' => $userId,
                'created_at' => $effectiveCreatedAt,
            ]);

            $this->recalculateBrandBalances($brand->id);
            $tx->refresh();

            $serviceRequest->update(['is_brand_ledger_posted' => true]);

            AuditLogService::log(
                action: 'created',
                module: 'brand_ledger',
                recordId: $tx->id,
                description: "Posted brand billing of Rs. {$rate} for {$brand->name} ({$serviceRequest->sr_number})",
                userId: $userId
            );

            return $tx;
        });
    }

    /**
     * Post a Payment received from Brand (Credit reduces receivable).
     */
    public function postPayment(
        Brand $brand,
        float $amount,
        string $paymentMethod,
        string $paymentDate,
        ?string $refNo = null,
        ?string $bankName = null,
        ?string $notes = null,
        ?int $userId = null
    ): BrandPayment {
        if ($amount <= 0) {
            throw new Exception("Payment amount must be greater than zero.");
        }

        return DB::transaction(function () use ($brand, $amount, $paymentMethod, $paymentDate, $refNo, $bankName, $notes, $userId) {
            $brandLocked = Brand::lockForUpdate()->findOrFail($brand->id);

            $paymentCreatedAt = Carbon::parse($paymentDate)->midDay();

            $tx = BrandLedger::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'brand_id' => $brandLocked->id,
                'service_request_id' => null,
                'transaction_type' => 'payment_received',
                'debit' => 0.00,
                'credit' => $amount,
                'running_balance' => 0.00,
                'description' => "Payment received via {$paymentMethod}" . ($refNo ? " (Ref: {$refNo})" : ''),
                'reference_number' => $refNo,
                'created_by_user_id' => $userId,
                'created_at' => $paymentCreatedAt,
            ]);

            $payment = BrandPayment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'brand_id' => $brandLocked->id,
                'brand_ledger_id' => $tx->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'reference_number' => $refNo,
                'bank_name' => $bankName,
                'notes' => $notes,
                'created_by_user_id' => $userId,
            ]);

            $this->recalculateBrandBalances($brandLocked->id);
            $tx->refresh();

            AuditLogService::log(
                action: 'payment_received',
                module: 'brand_ledger',
                recordId: $payment->id,
                description: "Recorded payment of Rs. {$amount} from {$brandLocked->name} via {$paymentMethod}",
                userId: $userId
            );

            return $payment;
        });
    }

    /**
     * Post manual adjustment or debit note / credit note for Brand.
     */
    public function postManualTransaction(
        Brand $brand,
        string $transactionType, // debit_adjustment or credit_adjustment
        float $amount,
        string $description,
        ?string $refNo = null,
        ?int $userId = null
    ): BrandLedger {
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero.");
        }

        return DB::transaction(function () use ($brand, $transactionType, $amount, $description, $refNo, $userId) {
            $brandLocked = Brand::lockForUpdate()->findOrFail($brand->id);

            $isDebit = in_array($transactionType, ['job_billing', 'debit_adjustment']);
            $debit = $isDebit ? $amount : 0.00;
            $credit = !$isDebit ? $amount : 0.00;

            $tx = BrandLedger::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'brand_id' => $brandLocked->id,
                'service_request_id' => null,
                'transaction_type' => $transactionType,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => 0.00,
                'description' => $description,
                'reference_number' => $refNo,
                'created_by_user_id' => $userId,
            ]);

            $this->recalculateBrandBalances($brandLocked->id);
            $tx->refresh();

            AuditLogService::log(
                action: 'created',
                module: 'brand_ledger',
                recordId: $tx->id,
                description: "Manual brand ledger entry [{$transactionType}] Rs. {$amount} for {$brandLocked->name}",
                userId: $userId
            );

            return $tx;
        });
    }

    /**
     * Reverse Brand Ledger Transaction safely.
     */
    public function reverseTransaction(BrandLedger $transaction, string $reason, int $userId): BrandLedger
    {
        if ($transaction->is_reversed) {
            throw new Exception("Transaction has already been reversed.");
        }

        return DB::transaction(function () use ($transaction, $reason, $userId) {
            $brandLocked = Brand::lockForUpdate()->findOrFail($transaction->brand_id);

            $reverseDebit = (float)$transaction->credit;
            $reverseCredit = (float)$transaction->debit;

            $reversalTx = BrandLedger::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'brand_id' => $brandLocked->id,
                'service_request_id' => $transaction->service_request_id,
                'transaction_type' => 'reversal',
                'debit' => $reverseDebit,
                'credit' => $reverseCredit,
                'running_balance' => 0.00,
                'is_reversed' => true,
                'description' => "REVERSAL of {$transaction->transaction_number}: {$reason}",
                'reference_number' => $transaction->transaction_number,
                'created_by_user_id' => $userId,
            ]);

            $transaction->update([
                'is_reversed' => true,
                'reversed_by_user_id' => $userId,
                'reversal_transaction_id' => $reversalTx->id,
                'reversal_reason' => $reason,
            ]);

            $this->recalculateBrandBalances($brandLocked->id);
            $reversalTx->refresh();

            if ($transaction->service_request_id) {
                ServiceRequest::where('id', $transaction->service_request_id)->update(['is_brand_ledger_posted' => false]);
            }

            AuditLogService::log(
                action: 'updated',
                module: 'brand_ledger',
                recordId: $reversalTx->id,
                description: "Reversed brand transaction {$transaction->transaction_number} for {$brandLocked->name}. Reason: {$reason}",
                userId: $userId
            );

            return $reversalTx;
        });
    }

    public function generateTransactionNumber(): string
    {
        $year = date('Y');
        $maxTx = BrandLedger::where('transaction_number', 'like', "BTX-{$year}-%")
            ->orderBy('id', 'desc')
            ->value('transaction_number');

        $nextSeq = 1;
        if ($maxTx && preg_match('/BTX-\d+-(\d+)/', $maxTx, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        }

        do {
            $num = sprintf('BTX-%s-%05d', $year, $nextSeq);
            $nextSeq++;
        } while (BrandLedger::where('transaction_number', $num)->exists());

        return $num;
    }

    public function generatePaymentNumber(): string
    {
        $year = date('Y');
        $maxPay = BrandPayment::where('payment_number', 'like', "BPAY-{$year}-%")
            ->orderBy('id', 'desc')
            ->value('payment_number');

        $nextSeq = 1;
        if ($maxPay && preg_match('/BPAY-\d+-(\d+)/', $maxPay, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        }

        do {
            $num = sprintf('BPAY-%s-%05d', $year, $nextSeq);
            $nextSeq++;
        } while (BrandPayment::where('payment_number', $num)->exists());

        return $num;
    }
}
