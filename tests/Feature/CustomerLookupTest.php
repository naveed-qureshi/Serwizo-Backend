<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_search_by_phone_returns_matching_customer(): void
    {
        $user = User::create([
            'name' => 'Ops',
            'email' => 'ops@test.com',
            'password' => bcrypt('password123'),
            'role' => 'operations',
            'is_active' => true,
        ]);

        $city = City::create(['name' => 'Lahore', 'status' => 'active']);

        $customer = Customer::create([
            'customer_code' => 'SW-C-1001',
            'name' => 'Dr. Kamran Malik',
            'phone' => '03001239876',
            'city_id' => $city->id,
            'address' => 'Phase 3 DHA Lahore',
        ]);

        $response = $this->actingAs($user)->getJson('/ajax/customers/search?phone=03001239876');

        $response->assertStatus(200)
            ->assertJson([
                'found' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => 'Dr. Kamran Malik',
                    'phone' => '03001239876',
                ]
            ]);
    }
}
