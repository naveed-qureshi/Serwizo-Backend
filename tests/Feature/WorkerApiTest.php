<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\ServiceJob;
use App\Models\ServiceRequest;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkerApiTest extends TestCase
{
    use RefreshDatabase;

    protected Worker $worker;
    protected ServiceRequest $jobRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $city = City::create(['name' => 'Lahore', 'status' => 'active']);
        $category = Category::create(['title' => 'Appliances', 'slug' => 'appliances', 'status' => 'active']);

        $serviceJob = ServiceJob::create([
            'category_id' => $category->id,
            'title' => 'AC Cleaning',
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
            'primary_city_id' => $city->id,
            'status' => 'active',
            'availability' => 'available',
            'password' => bcrypt('123456'),
        ]);

        $customer = Customer::create([
            'customer_code' => 'SW-C-1001',
            'name' => 'Kamran',
            'phone' => '03009876543',
            'city_id' => $city->id,
            'address' => 'DHA Lahore',
        ]);

        $this->jobRequest = ServiceRequest::create([
            'sr_number' => 'SR-260901-0001',
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'category_id' => $category->id,
            'service_job_id' => $serviceJob->id,
            'service_address' => 'DHA Lahore',
            'status' => 'assigned',
            'current_worker_id' => $this->worker->id,
            'customer_price' => 2500,
            'worker_payment' => 2000,
            'commission_amount' => 500,
        ]);
    }

    public function test_worker_can_login_via_api(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '03011234567',
            'password' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'worker' => ['id', 'worker_code', 'name', 'phone'],
                ]
            ]);
    }

    public function test_worker_can_fetch_assigned_jobs(): void
    {
        $token = $this->worker->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/worker/jobs?filter=active');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'sr_number', 'title', 'status', 'worker_wage']
                ]
            ]);
    }

    public function test_worker_can_accept_job(): void
    {
        $token = $this->worker->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/jobs/{$this->jobRequest->id}/accept");

        $response->assertStatus(200);
        $this->assertEquals('accepted', $this->jobRequest->fresh()->status);
    }

    public function test_worker_can_upload_proof_and_complete_job(): void
    {
        Storage::fake('public');
        $token = $this->worker->createToken('test-token')->plainTextToken;

        // Accept first
        $this->jobRequest->update(['status' => 'in_progress']);

        // Upload proof
        $file = UploadedFile::fake()->image('after_work.jpg');
        $proofRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/jobs/{$this->jobRequest->id}/proof", [
                'proof_type' => 'completion',
                'image' => $file,
                'caption' => 'AC fully serviced and cooling verified',
            ]);

        $proofRes->assertStatus(200);
        $this->assertDatabaseHas('job_proofs', [
            'service_request_id' => $this->jobRequest->id,
            'worker_id' => $this->worker->id,
            'proof_type' => 'completion',
        ]);

        // Complete Job
        $completeRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/jobs/{$this->jobRequest->id}/complete");

        $completeRes->assertStatus(200);
        $this->assertEquals('completed', $this->jobRequest->fresh()->status);
        // Cash job: worker collected Rs. 2500, owes Rs. 500 commission
        $this->assertEquals(-500.00, $this->worker->fresh()->getLedgerBalance());
    }
}
