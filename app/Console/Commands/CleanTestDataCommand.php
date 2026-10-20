<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CleanTestDataCommand extends Command
{
    protected $signature = 'data:clean-test-data {--workers : Also delete workers and their documents} {--force : Skip confirmation prompt}';
    protected $description = 'Safely wipe all transactional test data (requests, proofs, ledgers, customers) while preserving master system configuration';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('WARNING: This will permanently delete all test service requests, photos, ledgers, customers, and audit logs. Continue?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Cleaning test data from database...');

        Schema::disableForeignKeyConstraints();

        // 1. Delete Job Proofs & storage files
        $this->line('• Deleting test proof photos...');
        DB::table('job_proofs')->truncate();
        Storage::disk('public')->deleteDirectory('uploads/jobs/proofs');
        Storage::disk('public')->makeDirectory('uploads/jobs/proofs');

        // 2. Delete Request History & Assignments
        $this->line('• Deleting status history and assignments...');
        DB::table('service_request_status_histories')->truncate();
        DB::table('worker_assignments')->truncate();
        if (Schema::hasTable('worker_assignment_histories')) {
            DB::table('worker_assignment_histories')->truncate();
        }

        // 3. Delete Ledgers & Payments
        $this->line('• Deleting worker and brand ledgers & payments...');
        DB::table('ledger_transactions')->truncate();
        DB::table('brand_ledgers')->truncate();
        DB::table('worker_payments')->truncate();
        if (Schema::hasTable('brand_payments')) {
            DB::table('brand_payments')->truncate();
        }

        // 4. Delete Service Requests
        $this->line('• Deleting service requests...');
        DB::table('service_requests')->truncate();

        // 5. Delete Audit Logs & Notifications
        $this->line('• Deleting audit logs and notifications...');
        DB::table('audit_logs')->truncate();
        DB::table('app_notifications')->truncate();

        // 6. Delete Customers
        $this->line('• Deleting sample customers & addresses...');
        DB::table('customer_addresses')->truncate();
        DB::table('customers')->truncate();

        // 7. Optional: Delete Workers
        if ($this->option('workers')) {
            $this->line('• Deleting workers, documents, and bank details...');
            DB::table('worker_bank_details')->truncate();
            DB::table('worker_documents')->truncate();
            DB::table('worker_categories')->truncate();
            DB::table('worker_service_areas')->truncate();
            if (Schema::hasTable('worker_references')) {
                DB::table('worker_references')->truncate();
            }
            DB::table('workers')->truncate();
            Storage::disk('public')->deleteDirectory('uploads/workers');
            Storage::disk('public')->makeDirectory('uploads/workers');
        }

        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info('✓ System cleaned successfully!');
        $this->info('✓ All transactional test records and uploaded test proof photos have been wiped.');
        $this->info('✓ Master configuration preserved: Admin Users, Roles, Cities, Categories, Service Jobs, Brands.');
        $this->info('✓ Next Service Request will start fresh at SR-26-000001.');

        return 0;
    }
}
