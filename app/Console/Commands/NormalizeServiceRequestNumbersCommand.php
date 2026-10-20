<?php

namespace App\Console\Commands;

use App\Models\BrandLedger;
use App\Models\LedgerTransaction;
use App\Models\ServiceRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeServiceRequestNumbersCommand extends Command
{
    protected $signature = 'sr:normalize-numbers {--dry-run : Preview SR number standardizations without modifying database}';
    protected $description = 'Normalize legacy or mobile app generated SR numbers (e.g. SR-261002-4818) to the standard sequence format (SR-26-XXXXXX)';

    public function handle()
    {
        $isDryRun = (bool)$this->option('dry-run');

        // Find service requests that do NOT match the standard regex SR-\d{2}-\d{6}
        $nonStandardSRs = ServiceRequest::withTrashed()
            ->where('sr_number', 'NOT REGEXP', '^SR-[0-9]{2}-[0-9]{6}$')
            ->orderBy('id', 'asc')
            ->get();

        if ($nonStandardSRs->isEmpty()) {
            $this->info("✓ All Service Request numbers already conform to standard sequence (SR-YY-XXXXXX).");
            return 0;
        }

        $this->warn(sprintf("Found %d non-standard Service Request number(s).", $nonStandardSRs->count()));

        $year = date('y');
        $prefix = "SR-{$year}";

        // Find highest existing sequence in DB
        $highestSeq = 0;
        $standardSRs = ServiceRequest::withTrashed()
            ->where('sr_number', 'REGEXP', '^SR-[0-9]{2}-[0-9]{6}$')
            ->get();

        foreach ($standardSRs as $sr) {
            if (preg_match('/^SR-\d{2}-(\d{6})$/', $sr->sr_number, $m)) {
                $seq = (int)$m[1];
                if ($seq > $highestSeq) {
                    $highestSeq = $seq;
                }
            }
        }

        $plan = [];
        $currentSeq = $highestSeq;

        foreach ($nonStandardSRs as $sr) {
            $currentSeq++;
            $newSrNumber = sprintf('%s-%06d', $prefix, $currentSeq);
            $plan[] = [
                'id' => $sr->id,
                'old_sr' => $sr->sr_number,
                'new_sr' => $newSrNumber,
                'customer' => $sr->customer?->name ?? 'N/A',
                'status' => $sr->status,
            ];
        }

        $this->table(
            ['ID', 'Current SR Number', 'Normalized Standard SR Number', 'Customer', 'Status'],
            $plan
        );

        if ($isDryRun) {
            $this->info("Dry run complete. Run without --dry-run to apply changes.");
            return 0;
        }

        DB::transaction(function () use ($plan) {
            foreach ($plan as $item) {
                $sr = ServiceRequest::withTrashed()->find($item['id']);
                if (!$sr) continue;

                $old = $item['old_sr'];
                $new = $item['new_sr'];

                // 1. Update Service Request record
                $sr->update(['sr_number' => $new]);

                // 2. Update references in Worker Ledger
                LedgerTransaction::where('reference_number', $old)
                    ->update(['reference_number' => $new]);
                DB::table('ledger_transactions')
                    ->where('description', 'LIKE', "%{$old}%")
                    ->update([
                        'description' => DB::raw("REPLACE(description, '{$old}', '{$new}')")
                    ]);

                // 3. Update references in Brand Ledger
                BrandLedger::where('reference_number', $old)
                    ->update(['reference_number' => $new]);
                DB::table('brand_ledgers')
                    ->where('description', 'LIKE', "%{$old}%")
                    ->update([
                        'description' => DB::raw("REPLACE(description, '{$old}', '{$new}')")
                    ]);

                $this->info("✓ Updated {$old} -> {$new}");
            }
        });

        $this->newLine();
        $this->info("✓ All non-standard SR numbers have been successfully normalized to standard sequence!");

        return 0;
    }
}
