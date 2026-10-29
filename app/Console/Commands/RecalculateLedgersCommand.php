<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\BrandLedger;
use App\Models\LedgerTransaction;
use App\Models\Worker;
use App\Services\BrandLedgerService;
use App\Services\WorkerLedgerService;
use Illuminate\Console\Command;

class RecalculateLedgersCommand extends Command
{
    protected $signature = 'ledger:recalculate 
                            {--worker= : Specific worker ID to recalculate}
                            {--brand= : Specific brand ID to recalculate}';

    protected $description = 'Recalculate running balances for workers and brands, correctly ignoring reversed transactions';

    public function __construct(
        protected WorkerLedgerService $workerLedgerService,
        protected BrandLedgerService $brandLedgerService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $workerId = $this->option('worker');
        $brandId = $this->option('brand');

        $this->info("Starting ledger balance recalculation...");

        // 1. Worker Ledgers
        if ($workerId) {
            $worker = Worker::find($workerId);
            if ($worker) {
                $this->info("Recalculating Worker #{$worker->id} ({$worker->name})...");
                $this->workerLedgerService->recalculateWorkerBalances($worker->id);
                $this->info("✓ Worker #{$worker->id} balance is now: Rs. " . number_format($worker->fresh()->getLedgerBalance(), 2));
            } else {
                $this->error("Worker ID {$workerId} not found.");
            }
        } elseif (!$brandId) {
            // Recalculate all workers that have ledger transactions
            $workerIds = LedgerTransaction::distinct()->pluck('worker_id')->filter();
            $this->info("Recalculating {$workerIds->count()} worker ledgers...");
            $bar = $this->output->createProgressBar($workerIds->count());
            foreach ($workerIds as $wId) {
                $this->workerLedgerService->recalculateWorkerBalances((int)$wId);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->info("✓ All worker ledgers recalculated successfully.");
        }

        // 2. Brand Ledgers
        if ($brandId) {
            $brand = Brand::find($brandId);
            if ($brand) {
                $this->info("Recalculating Brand #{$brand->id} ({$brand->name})...");
                $this->brandLedgerService->recalculateBrandBalances($brand->id);
                $this->info("✓ Brand #{$brand->id} balance is now: Rs. " . number_format($brand->fresh()->getLedgerBalance(), 2));
            } else {
                $this->error("Brand ID {$brandId} not found.");
            }
        } elseif (!$workerId) {
            // Recalculate all brands that have ledger transactions
            $brandIds = BrandLedger::distinct()->pluck('brand_id')->filter();
            $this->info("Recalculating {$brandIds->count()} brand ledgers...");
            $bar = $this->output->createProgressBar($brandIds->count());
            foreach ($brandIds as $bId) {
                $this->brandLedgerService->recalculateBrandBalances((int)$bId);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->info("✓ All brand ledgers recalculated successfully.");
        }

        $this->info("Ledger recalculation complete!");
        return 0;
    }
}
