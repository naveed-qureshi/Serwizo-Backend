<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\ServiceJob;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerCategory;
use App\Models\WorkerServiceArea;
use App\Services\ServiceRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $opsUser;
    protected Worker $worker;
    protected City $city;
    protected Category $category;
    protected ServiceJob $job;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->opsUser = User::create([
            'name' => 'Ops User',
            'email' => 'ops@test.com',
            'password' => bcrypt('password123'),
            'role' => 'operations',
            'is_active' => true,
        ]);

        $this->city = City::create(['name' => 'Lahore', 'status' => 'active']);
        $this->category = Category::create(['title' => 'Appliances', 'slug' => 'appliances', 'status' => 'active']);

        $this->job = ServiceJob::create([
            'category_id' => $this->category->id,
            'title' => 'AC General Service',
            'default_customer_price' => 2500,
            'default_worker_payment' => 2000,
            'default_commission' => 500,
            'status' => 'active',
        ]);

        $this->worker = Worker::create([
            'worker_code' => 'SW-W-1001',
            'name' => 'Rashid Ali',
            'cnic_number' => '35201-1234567-1',
            'phone' => '03011234567',
            'primary_city_id' => $this->city->id,
            'status' => 'active',
            'availability' => 'available',
            'password' => bcrypt('123456'),
        ]);

        WorkerCategory::create([
            'worker_id' => $this->worker->id,
            'category_id' => $this->category->id,
            'is_primary' => true,
        ]);

        WorkerServiceArea::create([
            'worker_id' => $this->worker->id,
            'city_id' => $this->city->id,
            'is_primary' => true,
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'SW-C-1001',
            'name' => 'Dr. Kamran',
            'phone' => '03001239876',
            'city_id' => $this->city->id,
            'address' => 'Phase 5 DHA Lahore',
        ]);
    }

    public function test_full_service_request_workflow_progression(): void
    {
        $srService = app(ServiceRequestService::class);

        // 1. Create Service Request
        $sr = ServiceRequest::create([
            'sr_number' => $srService->generateSrNumber(),
            'customer_id' => $this->customer->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'service_job_id' => $this->job->id,
            'service_address' => 'Phase 5 DHA Lahore',
            'status' => 'new',
            'customer_price' => 2500,
            'worker_payment' => 2000,
            'commission_amount' => 500,
            'created_by_user_id' => $this->opsUser->id,
        ]);

        $this->assertEquals('new', $sr->status);

        // 2. Dispatch / Assign Worker
        $suitableWorkers = $srService->getSuitableWorkers($sr);
        $this->assertCount(1, $suitableWorkers);
        $this->assertEquals($this->worker->id, $suitableWorkers->first()->id);

        $srService->assignWorker($sr, $this->worker, $this->opsUser->id);
        $sr->refresh();

        $this->assertEquals('assigned', $sr->status);
        $this->assertEquals($this->worker->id, $sr->current_worker_id);
        $this->assertDatabaseHas('worker_assignments', [
            'service_request_id' => $sr->id,
            'worker_id' => $this->worker->id,
            'status' => 'pending',
        ]);

        // 3. Worker App acceptance & progress
        $srService->updateStatus($sr, 'accepted', null, $this->worker->id);
        $this->assertEquals('accepted', $sr->fresh()->status);

        $srService->updateStatus($sr, 'on_the_way', null, $this->worker->id);
        $this->assertEquals('on_the_way', $sr->fresh()->status);

        $srService->updateStatus($sr, 'in_progress', null, $this->worker->id);
        $this->assertEquals('in_progress', $sr->fresh()->status);

        // 4. Complete & Verify
        $srService->updateStatus($sr, 'completed', null, $this->worker->id);
        $sr->refresh();
        $this->assertEquals('completed', $sr->status);
        $this->assertTrue($sr->is_ledger_posted);

        // Verify ledger credit/debit for cash job: worker keeps cash, owes commission Rs. 500
        $this->assertEquals(-500.00, $this->worker->fresh()->getLedgerBalance());
    }

    public function test_super_admin_can_edit_and_delete_with_technical_reversal(): void
    {
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $srService = app(ServiceRequestService::class);

        // Create completed service request with posted ledger
        $sr = ServiceRequest::create([
            'sr_number' => $srService->generateSrNumber(),
            'customer_id' => $this->customer->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'service_job_id' => $this->job->id,
            'service_address' => 'Old Address',
            'status' => 'assigned',
            'current_worker_id' => $this->worker->id,
            'customer_price' => 3000,
            'worker_payment' => 2400,
            'commission_amount' => 600,
            'request_source' => 'phone',
            'created_by_user_id' => $adminUser->id,
        ]);

        $srService->updateStatus($sr, 'completed', null, $this->worker->id);
        $sr->refresh();

        $this->assertTrue($sr->is_ledger_posted);
        // Cash job: worker owes SerWizo Rs. 600 commission
        $this->assertEquals(-600.00, $this->worker->fresh()->getLedgerBalance());

        // 1. Super Admin updates request fields
        $response = $this->actingAs($adminUser)->put(route('service-requests.update', $sr), [
            'customer_name' => 'Dr. Kamran Updated',
            'customer_phone' => '03001239876',
            'customer_email' => 'kamran@test.com',
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'service_job_id' => $this->job->id,
            'service_address' => 'Updated Address 123',
            'request_source' => 'phone',
            'status' => 'completed',
            'current_worker_id' => $this->worker->id,
            'customer_price' => 3200,
            'worker_payment' => 2500,
            'commission_amount' => 700,
        ]);

        $response->assertRedirect(route('service-requests.show', $sr));
        $this->assertEquals('Updated Address 123', $sr->fresh()->service_address);

        // 2. Super Admin deletes request -> technical ledger reversal occurs
        $delResponse = $this->actingAs($adminUser)->delete(route('service-requests.destroy', $sr));
        $delResponse->assertRedirect(route('service-requests.index'));

        // Verify request is soft deleted
        $this->assertSoftDeleted('service_requests', ['id' => $sr->id]);

        // Verify ledger balance is reversed back to 0.00
        $this->assertEquals(0.00, $this->worker->fresh()->getLedgerBalance());

        // Verify a reversal transaction was recorded (restoring the Rs. 700 updated commission)
        $this->assertDatabaseHas('ledger_transactions', [
            'worker_id' => $this->worker->id,
            'transaction_type' => 'reversal',
            'credit' => 700.00,
        ]);
    }
}
