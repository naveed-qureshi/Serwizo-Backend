<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Category;
use App\Models\ServiceJob;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerCategory;
use App\Models\WorkerServiceArea;
use App\Services\WorkerLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkerDynamicLoginAndJobsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_worker_rashid_can_login_with_phone_and_pin()
    {
        // 1. Worker 1 (Rashid)
        $response1 = $this->postJson('/api/login', [
            'phone' => '03011234567',
            'password' => '123456',
        ]);

        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'worker' => [
                        'worker_code' => 'SW-W-1001',
                        'name' => 'Muhammad Rashid',
                        'mobile' => '03011234567',
                    ]
                ]
            ]);

        $token1 = $response1->json('data.token');

        // Check Worker 1's jobs
        $jobsRes1 = $this->withToken($token1)
            ->getJson('/api/worker/jobs');

        $jobsRes1->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check Worker 1's ledger
        $ledgerRes1 = $this->withToken($token1)
            ->getJson('/api/worker/ledger');

        $ledgerRes1->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_worker_tariq_can_login_with_phone_and_pin()
    {
        // 2. Worker 2 (Tariq Mehmood)
        $response2 = $this->postJson('/api/login', [
            'phone' => '03029876543',
            'password' => '123456',
        ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'worker' => [
                        'worker_code' => 'SW-W-1002',
                        'name' => 'Tariq Mehmood',
                        'mobile' => '03029876543',
                    ]
                ]
            ]);

        $token2 = $response2->json('data.token');

        // Tariq's profile
        $profileRes2 = $this->withToken($token2)
            ->getJson('/api/worker/profile');

        $profileRes2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'worker_code' => 'SW-W-1002',
                    'name' => 'Tariq Mehmood',
                ]
            ]);
    }

    public function test_newly_registered_backend_worker_can_login_and_access_dashboard()
    {
        $admin = User::where('role', 'super_admin')->first();
        $city = City::first();
        $category = Category::first();

        // Register worker via backend web controller / admin form
        $createResponse = $this->actingAs($admin)->post(route('workers.store'), [
            'name' => 'Asif Javed',
            'cnic_number' => '35201-8888999-1',
            'phone' => '03451122334',
            'email' => 'asif.javed@serwizo.com',
            'primary_city_id' => $city->id,
            'primary_category_id' => $category->id,
            'experience_years' => 4,
            'skill_level' => 'experienced',
            'status' => 'active',
            'availability' => 'available',
            'password' => 'secretPIN88',
        ]);

        $createResponse->assertRedirect();

        $worker = Worker::where('phone', '03451122334')->first();
        $this->assertNotNull($worker);
        $this->assertEquals('Asif Javed', $worker->name);

        // Reset web session auth so Sanctum bearer token auth is isolated
        auth()->logout();

        // Now log into Mobile App REST API with the new worker's credentials
        $loginRes = $this->postJson('/api/login', [
            'phone' => '03451122334',
            'password' => 'secretPIN88',
        ]);

        $loginRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'worker' => [
                        'worker_code' => $worker->worker_code,
                        'name' => 'Asif Javed',
                        'mobile' => '03451122334',
                        'status' => 'ACTIVE',
                    ]
                ]
            ]);

        $token = $loginRes->json('data.token');

        // Fetch new worker's profile
        $profileRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/worker/profile');

        $profileRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Asif Javed',
                    'mobile' => '03451122334',
                    'worker_code' => $worker->worker_code,
                ]
            ]);

        // Fetch empty jobs initially
        $jobsRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/worker/jobs');

        $jobsRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);

        // Initiate a free installation job as this new worker
        $initiateRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/worker/jobs/initiate', [
                'customer_name' => 'Hassan Raza',
                'customer_phone' => '03005554433',
                'service_address' => 'House 99, Street 4, Lahore',
                'is_free_installation' => true,
                'model_number' => 'HS-18INV',
                'serial_number' => 'SN-11223344',
                'start_immediately' => true,
            ]);

        $initiateRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'customer_name' => 'Hassan Raza',
                    'status' => 'in_progress',
                ]
            ]);

        // Verify that the job appears in Asif Javed's jobs list
        $jobsResAfter = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/worker/jobs');

        $jobsResAfter->assertStatus(200);
        $this->assertCount(1, $jobsResAfter->json('data'));
        $this->assertEquals('Hassan Raza', $jobsResAfter->json('data.0.customerName'));

        // Test completing the job using its string sr_number
        $srNumber = $initiateRes->json('data.sr_number');
        $completeRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/jobs/{$srNumber}/complete", [
                'notes' => 'Installation finished with 3 photos.'
            ]);

        $completeRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'completed'
                ]
            ]);

        // Check that the ledger reflects the credit immediately
        $ledgerRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/worker/ledger');

        $ledgerRes->assertStatus(200)
            ->assertJson(['success' => true]);
        
        $this->assertGreaterThan(0, $ledgerRes->json('data.summary.current_balance'));
        $this->assertCount(1, $ledgerRes->json('data.transactions'));
        $this->assertEquals('job_earning', $ledgerRes->json('data.transactions.0.type'));
        $this->assertGreaterThan(0, $ledgerRes->json('data.transactions.0.credit'));
    }
}
