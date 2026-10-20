<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\BrandLedger;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\ServiceJob;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Worker;
use App\Services\BrandLedgerService;
use App\Services\ServiceRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandManagementAndPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $brandUser;
    protected Brand $brand;
    protected Worker $worker;
    protected Customer $customer;
    protected City $city;
    protected Category $category;
    protected ServiceJob $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@serwizo.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->brand = Brand::create([
            'name' => 'Hisense',
            'code' => 'HISENSE',
            'default_installation_rate' => 2500.00,
            'status' => 'active',
        ]);

        $this->brandUser = User::create([
            'name' => 'Hisense Manager',
            'email' => 'portal@hisense.com',
            'password' => bcrypt('password123'),
            'role' => 'brand',
            'brand_id' => $this->brand->id,
            'is_active' => true,
        ]);

        $this->city = City::create(['name' => 'Lahore', 'province' => 'Punjab', 'status' => 'active']);
        $this->category = Category::create(['title' => 'Appliances', 'slug' => 'appliances', 'status' => 'active']);
        $this->job = ServiceJob::create([
            'category_id' => $this->category->id,
            'title' => 'AC Installation',
            'code' => 'AC-INST',
            'default_customer_price' => 0.00,
            'default_worker_payment' => 2000.00,
            'default_commission' => 500.00,
            'status' => 'active',
        ]);

        $this->worker = Worker::create([
            'worker_code' => 'SW-W-1001',
            'name' => 'Muhammad Rashid',
            'cnic_number' => '35201-1234567-1',
            'phone' => '03011234567',
            'primary_city_id' => $this->city->id,
            'status' => 'active',
            'availability' => 'available',
            'password' => bcrypt('123456'),
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'SW-C-1001',
            'name' => 'Dr. Kamran',
            'phone' => '03001239876',
            'city_id' => $this->city->id,
            'address' => 'DHA Phase 5, Lahore',
        ]);
    }

    public function test_admin_can_create_brand_with_portal_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('brands.store'), [
            'name' => 'Kenwood',
            'code' => 'KENWOOD',
            'default_installation_rate' => 2600.00,
            'status' => 'active',
            'create_user' => 1,
            'user_name' => 'Kenwood Admin',
            'user_email' => 'admin@kenwood.pk',
            'user_password' => 'password123',
        ]);

        $response->assertRedirect(route('brands.index'));
        $this->assertDatabaseHas('brands', ['code' => 'KENWOOD', 'status' => 'active']);
        $this->assertDatabaseHas('users', ['email' => 'admin@kenwood.pk', 'role' => 'brand']);
    }

    public function test_free_installation_job_posts_to_brand_ledger_and_worker_ledger(): void
    {
        $srService = app(ServiceRequestService::class);

        $sr = ServiceRequest::create([
            'sr_number' => $srService->generateSrNumber(),
            'customer_id' => $this->customer->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'service_job_id' => $this->job->id,
            'service_address' => 'DHA Lahore',
            'status' => 'assigned',
            'current_worker_id' => $this->worker->id,
            'is_free_installation' => true,
            'is_brand_job' => true,
            'brand_id' => $this->brand->id,
            'model_number' => 'HS-INV-1.5T',
            'serial_number' => 'SN-123456789',
            'quantity' => 1,
            'brand_rate' => 2500.00,
            'customer_price' => 0.00,
            'worker_payment' => 2000.00,
            'commission_amount' => 500.00,
            'created_by_user_id' => $this->admin->id,
        ]);

        // Worker completes job
        $srService->updateStatus($sr, 'completed', $this->admin->id);

        $this->worker->refresh();
        $this->assertEquals(2000.00, $this->worker->getLedgerBalance());
        $this->assertEquals(2500.00, $this->brand->getLedgerBalance());

        $this->assertDatabaseHas('brand_ledgers', [
            'brand_id' => $this->brand->id,
            'service_request_id' => $sr->id,
            'transaction_type' => 'job_billing',
            'debit' => 2500.00,
            'running_balance' => 2500.00,
        ]);
    }

    public function test_brand_user_can_access_brand_portal_and_see_only_their_jobs(): void
    {
        $otherBrand = Brand::create(['name' => 'TCL', 'code' => 'TCL', 'status' => 'active']);

        $sr1 = ServiceRequest::create([
            'sr_number' => 'SR-HISENSE-01',
            'customer_id' => $this->customer->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'service_job_id' => $this->job->id,
            'service_address' => 'Lahore',
            'status' => 'completed',
            'brand_id' => $this->brand->id,
            'is_brand_job' => true,
        ]);

        $sr2 = ServiceRequest::create([
            'sr_number' => 'SR-TCL-02',
            'customer_id' => $this->customer->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'service_job_id' => $this->job->id,
            'service_address' => 'Lahore',
            'status' => 'completed',
            'brand_id' => $otherBrand->id,
            'is_brand_job' => true,
        ]);

        // Brand user accesses brand portal jobs
        $response = $this->actingAs($this->brandUser)->get(route('brand-portal.jobs'));
        $response->assertStatus(200);
        $response->assertSee('SR-HISENSE-01');
        $response->assertDontSee('SR-TCL-02');
    }
}
