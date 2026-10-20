<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\LedgerTransaction;
use App\Models\ServiceJob;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Worker;
use App\Services\ServiceRequestService;
use App\Services\WorkerLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected Worker $worker;
    protected User $admin;
    protected ServiceJob $job;
    protected City $city;
    protected Customer $customer;
    protected WorkerLedgerService $ledgerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledgerService = app(WorkerLedgerService::class);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->city = City::create(['name' => 'Lahore', 'status' => 'active']);
        $cat = Category::create(['title' => 'Appliances', 'slug' => 'appliances', 'status' => 'active']);

        $this->job = ServiceJob::create([
            'category_id' => $cat->id,
            'title' => 'AC Service',
            'default_customer_price' => 2500,
            'default_worker_payment' => 2000,
            'default_commission' => 500,
            'status' => 'active',
        ]);

        $this->worker = Worker::create([
            'worker_code' => 'SW-W-1001',
            'name' => 'Rashid',
            'cnic_number' => '35201-1234567-1',
            'phone' => '03001234567',
            'primary_city_id' => $this->city->id,
            'status' => 'active',
            'availability' => 'available',
            'password' => bcrypt('123456'),
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'SW-C-1001',
            'name' => 'Kamran',
            'phone' => '03009876543',
            'city_id' => $this->city->id,
            'address' => 'DHA Lahore',
        ]);
    }

    public function test_initial_worker_ledger_balance_is_zero(): void
    {
        $this->assertEquals(0.00, $this->worker->getLedgerBalance());
    }

    public function test_cash_job_completion_posts_commission_deduction_debit_to_ledger(): void
    {
        $sr = ServiceRequest::create([
            'sr_number' => 'SR-260901-0001',
            'customer_id' => $this->customer->id,
            'city_id' => $this->city->id,
            'category_id' => $this->job->category_id,
            'service_job_id' => $this->job->id,
            'service_address' => 'DHA Lahore',
            'status' => 'new',
            'current_worker_id' => $this->worker->id,
            'customer_price' => 2500,
            'worker_payment' => 2000,
            'commission_amount' => 500,
            'is_free_installation' => false,
        ]);

        $srService = app(ServiceRequestService::class);
        $srService->updateStatus($sr, 'completed', $this->admin->id);

        $this->worker->refresh();
        // In a cash job, worker collected Rs. 2500 cash from customer directly, so worker owes SerWizo Rs. 500 commission
        $this->assertEquals(-500.00, $this->worker->getLedgerBalance());

        $this->assertDatabaseHas('ledger_transactions', [
            'worker_id' => $this->worker->id,
            'service_request_id' => $sr->id,
            'transaction_type' => 'commission_deduction',
            'debit' => 500.00,
            'running_balance' => -500.00,
        ]);
    }

    public function test_free_installation_job_completion_posts_earning_credit_to_ledger(): void
    {
        $sr = ServiceRequest::create([
            'sr_number' => 'SR-260901-0002',
            'customer_id' => $this->customer->id,
            'city_id' => $this->city->id,
            'category_id' => $this->job->category_id,
            'service_job_id' => $this->job->id,
            'service_address' => 'DHA Lahore',
            'status' => 'new',
            'current_worker_id' => $this->worker->id,
            'customer_price' => 0,
            'worker_payment' => 2000,
            'commission_amount' => 0,
            'is_free_installation' => true,
            'is_brand_job' => true,
            'brand_rate' => 2800,
        ]);

        $srService = app(ServiceRequestService::class);
        $srService->updateStatus($sr, 'completed', $this->admin->id);

        $this->worker->refresh();
        // In a free installation, customer pays Rs. 0, so worker is receivable Rs. 2000 from SerWizo
        $this->assertEquals(2000.00, $this->worker->getLedgerBalance());

        $this->assertDatabaseHas('ledger_transactions', [
            'worker_id' => $this->worker->id,
            'service_request_id' => $sr->id,
            'transaction_type' => 'job_earning',
            'credit' => 2000.00,
            'running_balance' => 2000.00,
        ]);
    }

    public function test_posting_worker_payment_debits_ledger_correctly(): void
    {
        // 1. Post earning of 2000
        $this->ledgerService->postManualTransaction(
            $this->worker,
            'job_earning',
            2000.00,
            'Test Job Earning',
            null,
            $this->admin->id
        );

        $this->assertEquals(2000.00, $this->worker->getLedgerBalance());

        // 2. Post payment of 1500
        $this->ledgerService->postPayment(
            $this->worker,
            1500.00,
            'bank_transfer',
            date('Y-m-d'),
            'IBFT-1001',
            'Meezan Bank',
            'Partial payout',
            $this->admin->id
        );

        $this->assertEquals(500.00, $this->worker->getLedgerBalance());

        // 3. Post remaining payment of 500
        $this->ledgerService->postPayment(
            $this->worker,
            500.00,
            'cash',
            date('Y-m-d'),
            null,
            null,
            'Final payout',
            $this->admin->id
        );

        $this->assertEquals(0.00, $this->worker->getLedgerBalance());
    }

    public function test_transaction_reversal_restores_balance_without_deleting_history(): void
    {
        $tx = $this->ledgerService->postManualTransaction(
            $this->worker,
            'bonus',
            1000.00,
            'Performance Bonus',
            null,
            $this->admin->id
        );

        $this->assertEquals(1000.00, $this->worker->getLedgerBalance());

        // Reverse the transaction
        $reversalTx = $this->ledgerService->reverseTransaction($tx, 'Posted in error', $this->admin->id);

        $this->assertEquals(0.00, $this->worker->getLedgerBalance());
        $this->assertTrue($tx->fresh()->is_reversed);
        $this->assertEquals('reversal', $reversalTx->transaction_type);
        $this->assertEquals(1000.00, $reversalTx->debit);
    }
}
