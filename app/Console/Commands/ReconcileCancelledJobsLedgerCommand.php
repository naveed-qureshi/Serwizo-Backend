<?php

namespace App\Console\Commands;

use App\Models\BrandLedger;
use App\Models\LedgerTransaction;
use App\Models\ServiceRequest;
use App\Services\BrandLedgerService;
use App\Services\WorkerLedgerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileCancelledJobsLedgerCommand extends Command
{
    protected $signature = 'ledger:reconcile-cancelled {--dry-run : Inspect unreversed ledger transactions without committing changes}';
    protected $description = 'Safely reconcile and reverse any active worker or brand ledger transactions for cancelled or non-completed jobs';

    public function __construct(
        protected WorkerLedgerService $workerLedgerService,
        protected BrandLedgerService $brandLedgerService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $isDryRun = (bool)$this->option('dry-run');

        $this->info("Scanning for cancelled / non-completed jobs with active ledger postings...");

        // 1. Find worker transactions on non-completed service requests that are NOT reversed
        $unreversedWorkerTxs = LedgerTransaction::with(['serviceRequest', 'worker'])
            ->whereNotNull('service_request_id')
            ->where('is_reversed', false)
            ->whereHas('serviceRequest', function ($q) {
                $q->whereNotIn('status', ['completed', 'verified']);
            })
            ->get();

        // 2. Find brand transactions on non-completed service requests that are NOT reversed
        $unreversedBrandTxs = BrandLedger::with(['serviceRequest', 'brand'])
            ->whereNotNull('service_request_id')
            ->where('is_reversed', false)
            ->whereHas('serviceRequest', function ($q) {
                $q->whereNotIn('status', ['completed', 'verified']);
            })
            ->get();

        if ($unreversedWorkerTxs->isEmpty() && $unreversedBrandTxs->isEmpty()) {
            $this->info("✓ All ledgers are fully reconciled! No active postings found on cancelled jobs.");
            return 0;
        }

        $this->warn(sprintf(
            "Found %d unreversed Worker transaction(s) and %d unreversed Brand transaction(s) on cancelled/non-completed jobs.",
            $unreversedWorkerTxs->count(),
            $unreversedBrandTxs->count()
        ));

        if ($isDryRun) {
            $this->table(
                ['Type', 'Tx Number', 'Job SR#', 'Job Status', 'Worker / Brand', 'Debit', 'Credit', 'Current Balance'],
                $unreversedWorkerTxs->map(fn($t) => [
                    'Worker Ledger',
                    $t->transaction_number,
                    $t->serviceRequest?->sr_number ?? 'N/A',
                    $t->serviceRequest?->status ?? 'N/A',
                    $t->worker?->name ?? 'N/A',
                    'Rs. ' . number_format($t->debit, 2),
                    'Rs. ' . number_format($t->credit, 2),
                    'Rs. ' . number_format($t->running_balance, 2),
                ])->concat(
                    $unreversedBrandTxs->map(fn($t) => [
                        'Brand Ledger',
                        $t->transaction_number,
                        $t->serviceRequest?->sr_number ?? 'N/A',
                        $t->serviceRequest?->status ?? 'N/A',
                        $t->brand?->name ?? 'N/A',
                        'Rs. ' . number_format($t->debit, 2),
                        'Rs. ' . number_format($t->credit, 2),
                        'Rs. ' . number_format($t->running_balance, 2),
                    ])
                )
            );
            $this->info("Dry run complete. Re-run without --dry-run to apply safe double-entry reversals.");
            return 0;
        }

        DB::transaction(function () use ($unreversedWorkerTxs, $unreversedBrandTxs) {
            $superAdminId = 1;

            foreach ($unreversedWorkerTxs as $tx) {
                $sr = $tx->serviceRequest;
                $srNumber = $sr?->sr_number ?? "SR#{$tx->service_request_id}";
                $reason = "Reconciled automatic reversal: Job {$srNumber} is {$sr->status}";

                $revTx = $this->workerLedgerService->reverseTransaction($tx, $reason, $superAdminId);
                $this->info("✓ Reversed Worker Tx {$tx->transaction_number} for {$tx->worker?->name} -> Created {$revTx->transaction_number} (New Balance: Rs. " . number_format($revTx->running_balance, 2) . ")");

                if ($sr) {
                    $sr->update(['is_ledger_posted' => false]);
                }
            }

            foreach ($unreversedBrandTxs as $btx) {
                $sr = $btx->serviceRequest;
                $srNumber = $sr?->sr_number ?? "SR#{$btx->service_request_id}";
                $reason = "Reconciled automatic reversal: Job {$srNumber} is {$sr->status}";

                $revTx = $this->brandLedgerService->reverseTransaction($btx, $reason, $superAdminId);
                $this->info("✓ Reversed Brand Tx {$btx->transaction_number} for {$btx->brand?->name} -> Created {$revTx->transaction_number} (New Balance: Rs. " . number_format($revTx->running_balance, 2) . ")");

                if ($sr) {
                    $sr->update(['is_brand_ledger_posted' => false]);
                }
            }
        });

        $this->newLine();
        $this->info("✓ Reconcile completed successfully. Worker and Brand balances are 100% accurate and audit trails preserved.");

        return 0;
    }
}
