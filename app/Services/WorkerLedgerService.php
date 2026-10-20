<?php

namespace App\Services;

use App\Models\LedgerTransaction;
use App\Models\ServiceRequest;
use App\Models\Worker;
use App\Models\WorkerPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class WorkerLedgerService
{
    /**
     * Recalculate all running balances for a Worker in chronological order.
     */
    public function recalculateWorkerBalances(int $workerId): void
    {
        $transactions = LedgerTransaction::where('worker_id', $workerId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $running = 0.00;
        foreach ($transactions as $tx) {
            $running = $running + (float)$tx->credit - (float)$tx->debit;
            if (abs((float)$tx->running_balance - $running) > 0.0001) {
                $tx->update(['running_balance' => $running]);
            }
        }
    }

    /**
     * Post Job Transaction to Worker Ledger on completion/verification.
     * Model:
     * - If Worker Wage > Customer Cash Collected: SerWizo pays Worker the difference (+Credit to worker, Receivable).
     * - If Customer Cash Collected > Worker Wage: Worker owes SerWizo commission difference (-Debit to worker, Payable).
     * - If Wage == Customer Cash: Balanced (no net deduction/addition).
     */
    public function postJobEarning(ServiceRequest $serviceRequest, ?int $userId = null): ?LedgerTransaction
    {
        return $this->syncJobEarning($serviceRequest, $userId);
    }

    /**
     * Synchronize Worker Ledger whenever a job is completed, verified, or edited.
     */
    public function syncJobEarning(ServiceRequest $serviceRequest, ?int $userId = null): ?LedgerTransaction
    {
        $workerId = $serviceRequest->current_worker_id;
        $isEligible = (bool)(
            $workerId &&
            in_array($serviceRequest->status, ['completed', 'verified'])
        );

        $existingTx = LedgerTransaction::where('service_request_id', $serviceRequest->id)
            ->where('is_reversed', false)
            ->first();

        if (!$isEligible) {
            if ($existingTx) {
                $oldWorkerId = $existingTx->worker_id;
                $this->reverseTransaction(
                    $existingTx,
                    "Reversal: Job {$serviceRequest->sr_number} status is no longer completed/verified or unassigned",
                    $userId ?? 1
                );
                $this->recalculateWorkerBalances($oldWorkerId);
            }
            if ($serviceRequest->is_ledger_posted) {
                $serviceRequest->update(['is_ledger_posted' => false]);
            }
            return null;
        }

        return DB::transaction(function () use ($serviceRequest, $workerId, $existingTx, $userId) {
            $worker = Worker::lockForUpdate()->findOrFail($workerId);

            $customerPrice = (float)$serviceRequest->customer_price;
            $workerWage = (float)$serviceRequest->worker_payment;
            $netDiff = $workerWage - $customerPrice; // positive = Serwizo owes worker, negative = worker collected cash & owes commission

            $effectiveCreatedAt = $serviceRequest->completed_at ?? $serviceRequest->verified_at ?? now();

            // Prepare description and tx type
            if ($netDiff > 0.001) {
                $txType = 'job_earning';
                $debit = 0.00;
                $credit = round($netDiff, 2);

                if ($serviceRequest->is_free_installation) {
                    $desc = "Free Installation Earning: {$serviceRequest->sr_number} - " . ($serviceRequest->serviceJob?->title ?? 'Installation');
                } elseif ($serviceRequest->job_nature === 'brand_after_sale') {
                    $desc = "Brand After-Sale Earning: {$serviceRequest->sr_number} (Wage Rs. {$workerWage}" . ($customerPrice > 0 ? ", Cash Collected Rs. {$customerPrice}" : "") . ")";
                } else {
                    $desc = "Job Earning: {$serviceRequest->sr_number} (Wage Rs. {$workerWage}" . ($customerPrice > 0 ? ", Cash Collected Rs. {$customerPrice}" : "") . ")";
                }
            } elseif ($netDiff < -0.001) {
                $txType = 'commission_deduction';
                $debit = round(abs($netDiff), 2);
                $credit = 0.00;
                $desc = "Commission Payable: {$serviceRequest->sr_number} (Collected Rs. {$customerPrice}, Wage Rs. {$workerWage})";
            } else {
                // Exactly 0 difference
                if ($existingTx) {
                    $oldWorkerId = $existingTx->worker_id;
                    $existingTx->delete();
                    $this->recalculateWorkerBalances($oldWorkerId);
                }
                $serviceRequest->update(['is_ledger_posted' => true]);
                return null;
            }

            if ($existingTx) {
                $oldWorkerId = $existingTx->worker_id;

                $existingTx->update([
                    'worker_id' => $worker->id,
                    'transaction_type' => $txType,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $desc,
                    'created_at' => $effectiveCreatedAt,
                ]);

                if ($oldWorkerId !== $worker->id) {
                    $this->recalculateWorkerBalances($oldWorkerId);
                }
                $this->recalculateWorkerBalances($worker->id);
                $existingTx->refresh();

                if (!$serviceRequest->is_ledger_posted) {
                    $serviceRequest->update(['is_ledger_posted' => true]);
                }

                AuditLogService::log(
                    action: 'updated',
                    module: 'ledger',
                    recordId: $existingTx->id,
                    description: "Updated ledger entry [{$txType}] for {$worker->name} ({$serviceRequest->sr_number})",
                    userId: $userId
                );

                return $existingTx;
            }

            // Create new transaction
            $tx = LedgerTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'worker_id' => $worker->id,
                'service_request_id' => $serviceRequest->id,
                'transaction_type' => $txType,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => 0.00,
                'description' => $desc,
                'reference_number' => $serviceRequest->sr_number,
                'created_by_user_id' => $userId,
                'created_at' => $effectiveCreatedAt,
            ]);

            $this->recalculateWorkerBalances($worker->id);
            $tx->refresh();

            $serviceRequest->update(['is_ledger_posted' => true]);

            AuditLogService::log(
                action: 'created',
                module: 'ledger',
                recordId: $tx->id,
                description: "Posted {$txType} of Rs. " . ($credit ?: $debit) . " for {$worker->name} ({$serviceRequest->sr_number})",
                userId: $userId
            );

            return $tx;
        });
    }

    /**
     * Post a Payment to Worker (Debit).
     */
    public function postPayment(
        Worker $worker,
        float $amount,
        string $paymentMethod,
        string $paymentDate,
        ?string $refNo = null,
        ?string $bankName = null,
        ?string $notes = null,
        ?int $userId = null
    ): WorkerPayment {
        if ($amount <= 0) {
            throw new Exception("Payment amount must be greater than zero.");
        }

        return DB::transaction(function () use ($worker, $amount, $paymentMethod, $paymentDate, $refNo, $bankName, $notes, $userId) {
            $workerLocked = Worker::lockForUpdate()->findOrFail($worker->id);

            $paymentCreatedAt = Carbon::parse($paymentDate)->midDay();

            $tx = LedgerTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'worker_id' => $workerLocked->id,
                'service_request_id' => null,
                'transaction_type' => 'payment',
                'debit' => $amount,
                'credit' => 0.00,
                'running_balance' => 0.00,
                'description' => "Payout via {$paymentMethod}" . ($refNo ? " (Ref: {$refNo})" : ''),
                'reference_number' => $refNo,
                'created_by_user_id' => $userId,
                'created_at' => $paymentCreatedAt,
            ]);

            $payment = WorkerPayment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'worker_id' => $workerLocked->id,
                'ledger_transaction_id' => $tx->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'reference_number' => $refNo,
                'bank_name' => $bankName,
                'notes' => $notes,
                'created_by_user_id' => $userId,
            ]);

            $this->recalculateWorkerBalances($workerLocked->id);
            $tx->refresh();

            AuditLogService::log(
                action: 'payment_posted',
                module: 'ledger',
                recordId: $payment->id,
                description: "Posted payment of Rs. {$amount} to {$workerLocked->name} via {$paymentMethod}",
                userId: $userId
            );

            return $payment;
        });
    }

    /**
     * Post an Advance / Bonus / Deduction / Adjustment.
     */
    public function postManualTransaction(
        Worker $worker,
        string $transactionType,
        float $amount,
        string $description,
        ?string $refNo = null,
        ?int $userId = null
    ): LedgerTransaction {
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero.");
        }

        return DB::transaction(function () use ($worker, $transactionType, $amount, $description, $refNo, $userId) {
            $workerLocked = Worker::lockForUpdate()->findOrFail($worker->id);

            $isCredit = in_array($transactionType, ['job_earning', 'bonus', 'refund']);
            $credit = $isCredit ? $amount : 0.00;
            $debit = !$isCredit ? $amount : 0.00;

            $tx = LedgerTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'worker_id' => $workerLocked->id,
                'service_request_id' => null,
                'transaction_type' => $transactionType,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => 0.00,
                'description' => $description,
                'reference_number' => $refNo,
                'created_by_user_id' => $userId,
            ]);

            $this->recalculateWorkerBalances($workerLocked->id);
            $tx->refresh();

            AuditLogService::log(
                action: 'created',
                module: 'ledger',
                recordId: $tx->id,
                description: "Manual ledger entry [{$transactionType}] Rs. {$amount} for {$workerLocked->name}",
                userId: $userId
            );

            return $tx;
        });
    }

    /**
     * Reverse / Void a transaction safely without destroying financial audit trail.
     */
    public function reverseTransaction(LedgerTransaction $transaction, string $reason, int $userId): LedgerTransaction
    {
        if ($transaction->is_reversed) {
            throw new Exception("Transaction has already been reversed.");
        }

        return DB::transaction(function () use ($transaction, $reason, $userId) {
            $workerLocked = Worker::lockForUpdate()->findOrFail($transaction->worker_id);

            // To reverse: swap debit and credit
            $reverseDebit = (float)$transaction->credit;
            $reverseCredit = (float)$transaction->debit;

            $reversalTx = LedgerTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'worker_id' => $workerLocked->id,
                'service_request_id' => $transaction->service_request_id,
                'transaction_type' => 'reversal',
                'debit' => $reverseDebit,
                'credit' => $reverseCredit,
                'running_balance' => 0.00,
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

            $this->recalculateWorkerBalances($workerLocked->id);
            $reversalTx->refresh();

            if ($transaction->service_request_id) {
                ServiceRequest::where('id', $transaction->service_request_id)->update(['is_ledger_posted' => false]);
            }

            AuditLogService::log(
                action: 'updated',
                module: 'ledger',
                recordId: $reversalTx->id,
                description: "Reversed transaction {$transaction->transaction_number} for {$workerLocked->name}. Reason: {$reason}",
                userId: $userId
            );

            return $reversalTx;
        });
    }

    public function generateTransactionNumber(): string
    {
        $year = date('Y');
        $maxTx = LedgerTransaction::where('transaction_number', 'like', "TX-{$year}-%")
            ->orderBy('id', 'desc')
            ->value('transaction_number');

        $nextSeq = 1;
        if ($maxTx && preg_match('/TX-\d+-(\d+)/', $maxTx, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        }

        do {
            $num = sprintf('TX-%s-%05d', $year, $nextSeq);
            $nextSeq++;
        } while (LedgerTransaction::where('transaction_number', $num)->exists());

        return $num;
    }

    public function generatePaymentNumber(): string
    {
        $year = date('Y');
        $maxPay = WorkerPayment::where('payment_number', 'like', "PAY-{$year}-%")
            ->orderBy('id', 'desc')
            ->value('payment_number');

        $nextSeq = 1;
        if ($maxPay && preg_match('/PAY-\d+-(\d+)/', $maxPay, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        }

        do {
            $num = sprintf('PAY-%s-%05d', $year, $nextSeq);
            $nextSeq++;
        } while (WorkerPayment::where('payment_number', $num)->exists());

        return $num;
    }
}
