<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\LedgerTransaction;
use App\Models\ServiceJob;
use App\Models\ServiceJobPrice;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestStatusHistory;
use App\Models\Setting;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerAssignment;
use App\Models\WorkerBankDetail;
use App\Models\WorkerCategory;
use App\Models\WorkerDocument;
use App\Models\WorkerReference;
use App\Models\WorkerServiceArea;
use App\Services\WorkerLedgerService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core Users with distinct roles
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@serwizo.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'phone' => '03001234567',
            'is_active' => true,
        ]);

        $opsUser = User::create([
            'name' => 'Operations Manager',
            'email' => 'operations@serwizo.com',
            'password' => Hash::make('password123'),
            'role' => 'operations',
            'phone' => '03007654321',
            'is_active' => true,
        ]);

        $accountsUser = User::create([
            'name' => 'Accounts Officer',
            'email' => 'accounts@serwizo.com',
            'password' => Hash::make('password123'),
            'role' => 'accounts',
            'phone' => '03119876543',
            'is_active' => true,
        ]);

        // 2. Settings
        Setting::set('app_name', 'SerWizo Service Management');
        Setting::set('currency', 'PKR');
        Setting::set('currency_symbol', 'Rs.');
        Setting::set('support_phone', '042-111-SERWIZO');
        Setting::set('support_email', 'support@serwizo.com');
        Setting::set('auto_post_ledger_on_verified', '1', 'financial', 'boolean');

        // 3. Cities & Areas
        $citiesData = [
            'Lahore' => [
                'province' => 'Punjab',
                'areas' => ['DHA Phase 1-6', 'Gulberg', 'Model Town', 'Johar Town', 'Bahria Town', 'Cantt', 'WAPDA Town', 'Faisal Town']
            ],
            'Karachi' => [
                'province' => 'Sindh',
                'areas' => ['Clifton', 'DHA Phase 1-8', 'Gulshan-e-Iqbal', 'North Nazimabad', 'PECHS', 'Bahria Town Karachi']
            ],
            'Islamabad' => [
                'province' => 'Federal',
                'areas' => ['F-6', 'F-7', 'F-10', 'G-11', 'E-11', 'Bahria Enclave', 'DHA Islamabad']
            ],
            'Rawalpindi' => [
                'province' => 'Punjab',
                'areas' => ['Saddar', 'Bahria Town Rawalpindi', 'Westridge', 'Satellite Town', 'Chaklala']
            ],
            'Faisalabad' => [
                'province' => 'Punjab',
                'areas' => ['Civil Lines', 'Madina Town', 'D Ground Peoples Colony', 'Kohinoor City']
            ],
            'Multan' => [
                'province' => 'Punjab',
                'areas' => ['Cantt', 'Gulgasht Colony', 'Bosan Road', 'Model Town']
            ],
            'Gujranwala' => [
                'province' => 'Punjab',
                'areas' => ['DC Colony', 'Model Town', 'Satellite Town', 'Wapda Town']
            ],
            'Peshawar' => [
                'province' => 'KPK',
                'areas' => ['Hayatabad', 'University Town', 'Cantt', 'Warsak Road']
            ],
        ];

        $createdCities = [];
        $createdAreas = [];

        foreach ($citiesData as $cityName => $info) {
            $city = City::create([
                'name' => $cityName,
                'province' => $info['province'],
                'status' => 'active',
            ]);
            $createdCities[$cityName] = $city;

            foreach ($info['areas'] as $areaName) {
                $area = Area::create([
                    'city_id' => $city->id,
                    'name' => $areaName,
                    'zone' => 'Urban',
                    'status' => 'active',
                ]);
                $createdAreas[$cityName][] = $area;
            }
        }

        // 4. Categories & Subcategories
        $categoriesData = [
            'Appliances' => [
                'icon' => 'fa-plug',
                'description' => 'Home appliances maintenance, installation and repair',
                'sub' => ['AC', 'Refrigerator', 'Washing Machine', 'Microwave', 'Deep Freezer', 'Water Dispenser', 'Geyser']
            ],
            'Electrician' => [
                'icon' => 'fa-bolt',
                'description' => 'Electrical wiring, fixtures, circuits and troubleshooting',
                'sub' => ['Wiring & Installation', 'Breaker & DB Box', 'Ceiling & Exhaust Fans', 'UPS & Generator Wiring', 'Lighting Fixtures']
            ],
            'Plumbing' => [
                'icon' => 'fa-wrench',
                'description' => 'Sanitary fitting, pipeline repairs, pumps and drain unblocking',
                'sub' => ['Pipe Leakage', 'Sanitary Fittings', 'Water Motor & Pumps', 'Geyser Installation', 'Drainage Solutions']
            ],
            'Cleaning' => [
                'icon' => 'fa-broom',
                'description' => 'Professional deep cleaning and fumigation services',
                'sub' => ['Full Home Deep Cleaning', 'Sofa & Carpet Shampooing', 'Water Tank Cleaning', 'Kitchen & Washroom Deep Clean', 'Fumigation & Pest Control']
            ],
            'Car Wash & Detailing' => [
                'icon' => 'fa-car',
                'description' => 'Doorstep waterless wash, polish and interior detailing',
                'sub' => ['Doorstep Car Wash', 'Interior Deep Detailing', 'Ceramic Polish & Wax', 'Engine Bay Cleaning']
            ],
            'Solar' => [
                'icon' => 'fa-sun',
                'description' => 'Solar panels installation, inverter configuration and cleaning',
                'sub' => ['Solar Panel Cleaning', 'Inverter Configuration', 'Structure & Wiring Check', 'Complete Solar Installation']
            ],
        ];

        $createdCategories = [];
        $createdSubCategories = [];

        foreach ($categoriesData as $catTitle => $cData) {
            $cat = Category::create([
                'title' => $catTitle,
                'slug' => Str::slug($catTitle),
                'description' => $cData['description'],
                'icon' => $cData['icon'],
                'status' => 'active',
            ]);
            $createdCategories[$catTitle] = $cat;

            foreach ($cData['sub'] as $subTitle) {
                $sub = SubCategory::create([
                    'category_id' => $cat->id,
                    'title' => $subTitle,
                    'slug' => Str::slug($subTitle),
                    'description' => "{$subTitle} services under {$catTitle}",
                    'status' => 'active',
                ]);
                $createdSubCategories[$catTitle][$subTitle] = $sub;
            }
        }

        // 5. Service Catalog Jobs & Default Pricing
        $jobsData = [
            [
                'cat' => 'Appliances',
                'sub' => 'AC',
                'title' => 'AC General Service (Split / Inverter)',
                'code' => 'APP-AC-01',
                'duration' => 60,
                'customer_price' => 2500,
                'worker_payment' => 2000,
                'commission' => 500,
                'desc' => 'Complete indoor/outdoor high pressure wash, filter cleaning, and gas check.'
            ],
            [
                'cat' => 'Appliances',
                'sub' => 'AC',
                'title' => 'AC Gas Refill (R410/R32/R22)',
                'code' => 'APP-AC-02',
                'duration' => 90,
                'customer_price' => 7500,
                'worker_payment' => 6000,
                'commission' => 1500,
                'desc' => 'Leakage testing, vacuuming, and complete refrigerant gas charging.'
            ],
            [
                'cat' => 'Appliances',
                'sub' => 'Refrigerator',
                'title' => 'Refrigerator Gas Refill & Cooling Repair',
                'code' => 'APP-REF-01',
                'duration' => 120,
                'customer_price' => 6000,
                'worker_payment' => 4800,
                'commission' => 1200,
                'desc' => 'Compressor inspection, thermostat check, capillary unblocking, and gas refill.'
            ],
            [
                'cat' => 'Appliances',
                'sub' => 'Washing Machine',
                'title' => 'Automatic Washing Machine Repair & Service',
                'code' => 'APP-WM-01',
                'duration' => 90,
                'customer_price' => 3500,
                'worker_payment' => 2800,
                'commission' => 700,
                'desc' => 'Drain valve, motor belt, PCB card diagnostic, and spin tub service.'
            ],
            [
                'cat' => 'Electrician',
                'sub' => 'Ceiling & Exhaust Fans',
                'title' => 'Ceiling Fan Installation & Capacitor Replacement',
                'code' => 'ELEC-FAN-01',
                'duration' => 45,
                'customer_price' => 1200,
                'worker_payment' => 950,
                'commission' => 250,
                'desc' => 'Ceiling bracket mounting, wiring connection, balancing, and speed test.'
            ],
            [
                'cat' => 'Electrician',
                'sub' => 'Breaker & DB Box',
                'title' => 'Main Distribution Board (DB) Troubleshooting',
                'code' => 'ELEC-DB-01',
                'duration' => 90,
                'customer_price' => 3000,
                'worker_payment' => 2400,
                'commission' => 600,
                'desc' => 'Tripping breaker identification, load distribution, and earthing inspection.'
            ],
            [
                'cat' => 'Plumbing',
                'sub' => 'Pipe Leakage',
                'title' => 'PPRC / CPVC Concealed Pipe Leakage Repair',
                'code' => 'PLUMB-LEAK-01',
                'duration' => 120,
                'customer_price' => 3500,
                'worker_payment' => 2800,
                'commission' => 700,
                'desc' => 'Wall opening, hot plate joint welding, and pressure testing.'
            ],
            [
                'cat' => 'Plumbing',
                'sub' => 'Water Motor & Pumps',
                'title' => 'Water Motor / Donkey Pump Repair & Overhaul',
                'code' => 'PLUMB-PUMP-01',
                'duration' => 90,
                'customer_price' => 2800,
                'worker_payment' => 2200,
                'commission' => 600,
                'desc' => 'Bearing replacement, seal fixing, winding check, and priming.'
            ],
            [
                'cat' => 'Cleaning',
                'sub' => 'Sofa & Carpet Shampooing',
                'title' => '7-Seater Sofa Deep Shampoo & Extraction',
                'code' => 'CLN-SOFA-01',
                'duration' => 120,
                'customer_price' => 4500,
                'worker_payment' => 3600,
                'commission' => 900,
                'desc' => 'Industrial foam shampooing, spot treatment, and high-power vacuum extraction.'
            ],
            [
                'cat' => 'Solar',
                'sub' => 'Solar Panel Cleaning',
                'title' => 'Solar Panels Deep Cleaning (Up to 10kW system)',
                'code' => 'SOLAR-CLN-01',
                'duration' => 90,
                'customer_price' => 3000,
                'worker_payment' => 2400,
                'commission' => 600,
                'desc' => 'De-ionized water washing, soft microfiber brushing, and efficiency check.'
            ],
        ];

        $createdJobs = [];
        foreach ($jobsData as $j) {
            $cat = $createdCategories[$j['cat']];
            $sub = $createdSubCategories[$j['cat']][$j['sub']] ?? null;

            $job = ServiceJob::create([
                'category_id' => $cat->id,
                'sub_category_id' => $sub?->id,
                'title' => $j['title'],
                'code' => $j['code'],
                'description' => $j['desc'],
                'estimated_duration_minutes' => $j['duration'],
                'default_customer_price' => $j['customer_price'],
                'default_worker_payment' => $j['worker_payment'],
                'default_commission' => $j['commission'],
                'status' => 'active',
            ]);
            $createdJobs[$j['code']] = $job;
        }

        // 6. Workers
        $lahore = $createdCities['Lahore'];
        $karachi = $createdCities['Karachi'];
        $isb = $createdCities['Islamabad'];

        $workersData = [
            [
                'code' => 'SW-W-1001',
                'name' => 'Muhammad Rashid',
                'cnic' => '35201-1234567-1',
                'phone' => '03011234567',
                'email' => 'rashid.worker@serwizo.com',
                'dob' => '1990-05-15',
                'address' => 'House 14, St 3, Model Town, Lahore',
                'city' => $lahore,
                'exp' => 8,
                'skill' => 'expert',
                'cat' => 'Appliances',
                'sub' => 'AC',
                'status' => 'active',
                'availability' => 'available',
                'bank' => [
                    'name' => 'Meezan Bank Ltd',
                    'title' => 'Muhammad Rashid',
                    'account' => '01020304050607',
                    'iban' => 'PK64MEZN0001020304050607',
                    'status' => 'verified'
                ],
                'ref' => [
                    ['name' => 'Haji Tariq', 'phone' => '03001112233', 'rel' => 'Previous Employer - Tariq Electronics'],
                    ['name' => 'Usman Ali', 'phone' => '03214445566', 'rel' => 'Colleague']
                ]
            ],
            [
                'code' => 'SW-W-1002',
                'name' => 'Tariq Mehmood',
                'cnic' => '35202-7654321-3',
                'phone' => '03029876543',
                'email' => 'tariq.electrician@serwizo.com',
                'dob' => '1988-10-20',
                'address' => 'Plot 45, Sector B, Bahria Town, Lahore',
                'city' => $lahore,
                'exp' => 10,
                'skill' => 'expert',
                'cat' => 'Electrician',
                'sub' => 'Wiring & Installation',
                'status' => 'active',
                'availability' => 'available',
                'bank' => [
                    'name' => 'Habib Bank Limited',
                    'title' => 'Tariq Mehmood',
                    'account' => '12345678901234',
                    'iban' => 'PK36HABB0012345678901234',
                    'status' => 'verified'
                ],
                'ref' => [
                    ['name' => 'Engr. Salman', 'phone' => '03335556677', 'rel' => 'Site Supervisor - Al-Raziq Builders']
                ]
            ],
            [
                'code' => 'SW-W-1003',
                'name' => 'Khurram Shehzad',
                'cnic' => '35201-9988776-5',
                'phone' => '03035551234',
                'email' => 'khurram.plumber@serwizo.com',
                'dob' => '1995-03-12',
                'address' => 'St 8, Main Bazar, Ichhra, Lahore',
                'city' => $lahore,
                'exp' => 5,
                'skill' => 'experienced',
                'cat' => 'Plumbing',
                'sub' => 'Pipe Leakage',
                'status' => 'active',
                'availability' => 'available',
                'bank' => [
                    'name' => 'Bank Alfalah',
                    'title' => 'Khurram Shehzad',
                    'account' => '554433221100',
                    'iban' => 'PK12ALFH0000554433221100',
                    'status' => 'verified'
                ],
                'ref' => [
                    ['name' => 'Master Aslam', 'phone' => '03009988776', 'rel' => 'Senior Plumber']
                ]
            ],
            [
                'code' => 'SW-W-1004',
                'name' => 'Farhan Qureshi',
                'cnic' => '42101-5544332-1',
                'phone' => '03123344556',
                'email' => 'farhan.karachi@serwizo.com',
                'dob' => '1992-07-25',
                'address' => 'Flat 302, Gulshan Block 4, Karachi',
                'city' => $karachi,
                'exp' => 6,
                'skill' => 'experienced',
                'cat' => 'Appliances',
                'sub' => 'AC',
                'status' => 'active',
                'availability' => 'available',
                'bank' => [
                    'name' => 'MCB Bank',
                    'title' => 'Farhan Qureshi',
                    'account' => '098765432112',
                    'iban' => 'PK88MUCB0000098765432112',
                    'status' => 'not_verified'
                ],
                'ref' => [
                    ['name' => 'Sohail Khan', 'phone' => '03212233445', 'rel' => 'Uncle']
                ]
            ],
            [
                'code' => 'SW-W-1005',
                'name' => 'Bilal Ahmed',
                'cnic' => '61101-1122334-9',
                'phone' => '03345566778',
                'email' => 'bilal.isb@serwizo.com',
                'dob' => '1996-11-05',
                'address' => 'House 12, G-11/3, Islamabad',
                'city' => $isb,
                'exp' => 4,
                'skill' => 'intermediate',
                'cat' => 'Solar',
                'sub' => 'Solar Panel Cleaning',
                'status' => 'active',
                'availability' => 'available',
                'bank' => [
                    'name' => 'Allied Bank Ltd',
                    'title' => 'Bilal Ahmed',
                    'account' => '887766554433',
                    'iban' => 'PK44ABPA0088776655443322',
                    'status' => 'verified'
                ],
                'ref' => [
                    ['name' => 'Imran Shah', 'phone' => '03004455667', 'rel' => 'Shop Owner']
                ]
            ],
        ];

        $createdWorkers = [];
        $ledgerService = app(WorkerLedgerService::class);

        foreach ($workersData as $w) {
            $worker = Worker::create([
                'worker_code' => $w['code'],
                'name' => $w['name'],
                'cnic_number' => $w['cnic'],
                'phone' => $w['phone'],
                'email' => $w['email'],
                'date_of_birth' => $w['dob'],
                'address' => $w['address'],
                'primary_city_id' => $w['city']->id,
                'experience_years' => $w['exp'],
                'skill_level' => $w['skill'],
                'status' => $w['status'],
                'availability' => $w['availability'],
                'password' => Hash::make('123456'), // Worker app PIN/password
                'last_active_at' => now(),
            ]);
            $createdWorkers[$w['code']] = $worker;

            // Categories
            $cat = $createdCategories[$w['cat']];
            $sub = $createdSubCategories[$w['cat']][$w['sub']] ?? null;
            WorkerCategory::create([
                'worker_id' => $worker->id,
                'category_id' => $cat->id,
                'sub_category_id' => $sub?->id,
                'is_primary' => true,
            ]);

            // Multi-city areas (Primary city + Nearby city if applicable)
            WorkerServiceArea::create([
                'worker_id' => $worker->id,
                'city_id' => $w['city']->id,
                'is_primary' => true,
            ]);

            if ($w['city']->name === 'Lahore') {
                $gujranwala = $createdCities['Gujranwala'];
                WorkerServiceArea::create([
                    'worker_id' => $worker->id,
                    'city_id' => $gujranwala->id,
                    'is_primary' => false,
                ]);
            }

            // Bank Details
            WorkerBankDetail::create([
                'worker_id' => $worker->id,
                'bank_name' => $w['bank']['name'],
                'account_title' => $w['bank']['title'],
                'account_number' => $w['bank']['account'],
                'iban' => $w['bank']['iban'],
                'verification_status' => $w['bank']['status'],
                'verified_by_user_id' => $w['bank']['status'] === 'verified' ? $superAdmin->id : null,
                'verified_at' => $w['bank']['status'] === 'verified' ? now() : null,
            ]);

            // References
            foreach ($w['ref'] as $r) {
                WorkerReference::create([
                    'worker_id' => $worker->id,
                    'name' => $r['name'],
                    'phone' => $r['phone'],
                    'relationship' => $r['rel'],
                ]);
            }

            // Document KYC records
            WorkerDocument::create([
                'worker_id' => $worker->id,
                'document_type' => 'cnic_front',
                'file_path' => 'uploads/workers/cnic/sample_cnic_front.jpg',
                'file_name' => 'cnic_front.jpg',
                'mime_type' => 'image/jpeg',
                'file_size_bytes' => 245000,
                'is_verified' => true,
                'verified_by_user_id' => $superAdmin->id,
                'verified_at' => now(),
            ]);

            WorkerDocument::create([
                'worker_id' => $worker->id,
                'document_type' => 'cnic_back',
                'file_path' => 'uploads/workers/cnic/sample_cnic_back.jpg',
                'file_name' => 'cnic_back.jpg',
                'mime_type' => 'image/jpeg',
                'file_size_bytes' => 230000,
                'is_verified' => true,
                'verified_by_user_id' => $superAdmin->id,
                'verified_at' => now(),
            ]);
        }

        // 7. Customers
        $customersData = [
            [
                'code' => 'SW-C-1001',
                'name' => 'Dr. Kamran Malik',
                'phone' => '03001239876',
                'email' => 'kamran.malik@gmail.com',
                'city' => $lahore,
                'address' => 'House 88-B, Sector Z, Phase 3, DHA, Lahore',
            ],
            [
                'code' => 'SW-C-1002',
                'name' => 'Syeda Fatima Zahra',
                'phone' => '03215556644',
                'email' => 'fatima.zahra@hotmail.com',
                'city' => $lahore,
                'address' => 'Flat 4B, Gulberg Heights, Main Boulevard Gulberg, Lahore',
            ],
            [
                'code' => 'SW-C-1003',
                'name' => 'Muhammad Ali Khan',
                'phone' => '03334447788',
                'email' => 'ali.khan@corporation.pk',
                'city' => $karachi,
                'address' => 'Bungalow 12, 26th Street, Phase 5 DHA, Karachi',
            ],
            [
                'code' => 'SW-C-1004',
                'name' => 'Zeeshan Baig',
                'phone' => '03456789012',
                'email' => 'zeeshan.baig@outlook.com',
                'city' => $isb,
                'address' => 'House 140, Street 22, F-10/2, Islamabad',
            ],
        ];

        $createdCustomers = [];
        foreach ($customersData as $c) {
            $customer = Customer::create([
                'customer_code' => $c['code'],
                'name' => $c['name'],
                'phone' => $c['phone'],
                'email' => $c['email'],
                'city_id' => $c['city']->id,
                'address' => $c['address'],
            ]);
            $createdCustomers[$c['code']] = $customer;

            CustomerAddress::create([
                'customer_id' => $customer->id,
                'city_id' => $c['city']->id,
                'title' => 'Home',
                'address_line' => $c['address'],
                'is_default' => true,
            ]);
        }

        // 8. Service Requests & Workflow History
        $c1 = $createdCustomers['SW-C-1001'];
        $c2 = $createdCustomers['SW-C-1002'];
        $w1 = $createdWorkers['SW-W-1001']; // Rashid AC
        $w2 = $createdWorkers['SW-W-1002']; // Tariq Elec
        $jobAc = $createdJobs['APP-AC-01'];
        $jobElec = $createdJobs['ELEC-DB-01'];

        // Request 1: Verified & Completed (Ledger Posted)
        $sr1 = ServiceRequest::create([
            'sr_number' => 'SR-260901-0001',
            'customer_id' => $c1->id,
            'city_id' => $lahore->id,
            'category_id' => $jobAc->category_id,
            'sub_category_id' => $jobAc->sub_category_id,
            'service_job_id' => $jobAc->id,
            'service_address' => $c1->address,
            'customer_complaint' => 'AC indoor unit making rattling noise and low cooling.',
            'special_instructions' => 'Bring extension cord and ladder.',
            'preferred_date' => Carbon::yesterday(),
            'preferred_time' => '11:00 AM - 01:00 PM',
            'request_source' => 'phone',
            'status' => 'verified',
            'current_worker_id' => $w1->id,
            'customer_price' => $jobAc->default_customer_price, // 2500
            'worker_payment' => $jobAc->default_worker_payment, // 2000
            'commission_amount' => $jobAc->default_commission,  // 500
            'is_ledger_posted' => true,
            'created_by_user_id' => $opsUser->id,
            'assigned_at' => Carbon::yesterday()->setHour(9),
            'completed_at' => Carbon::yesterday()->setHour(13),
            'verified_at' => Carbon::yesterday()->setHour(14),
            'verified_by_user_id' => $superAdmin->id,
            'created_at' => Carbon::yesterday()->setHour(8),
        ]);

        ServiceRequestStatusHistory::create([
            'service_request_id' => $sr1->id,
            'from_status' => null,
            'to_status' => 'new',
            'changed_by_user_id' => $opsUser->id,
            'notes' => 'Customer called to book service.',
            'created_at' => Carbon::yesterday()->setHour(8),
        ]);

        ServiceRequestStatusHistory::create([
            'service_request_id' => $sr1->id,
            'from_status' => 'new',
            'to_status' => 'assigned',
            'changed_by_user_id' => $opsUser->id,
            'notes' => "Assigned to {$w1->name}",
            'created_at' => Carbon::yesterday()->setHour(9),
        ]);

        ServiceRequestStatusHistory::create([
            'service_request_id' => $sr1->id,
            'from_status' => 'assigned',
            'to_status' => 'completed',
            'worker_id' => $w1->id,
            'notes' => 'Job completed and verified with customer.',
            'created_at' => Carbon::yesterday()->setHour(13),
        ]);

        ServiceRequestStatusHistory::create([
            'service_request_id' => $sr1->id,
            'from_status' => 'completed',
            'to_status' => 'verified',
            'changed_by_user_id' => $superAdmin->id,
            'notes' => 'Operations QA call completed. 5-star customer rating.',
            'created_at' => Carbon::yesterday()->setHour(14),
        ]);

        WorkerAssignment::create([
            'service_request_id' => $sr1->id,
            'worker_id' => $w1->id,
            'assigned_by_user_id' => $opsUser->id,
            'status' => 'completed',
            'assigned_at' => Carbon::yesterday()->setHour(9),
            'responded_at' => Carbon::yesterday()->setHour(9)->addMinutes(15),
        ]);

        // Post ledger for SR 1
        $tx1 = LedgerTransaction::create([
            'transaction_number' => 'TX-2026-00001',
            'worker_id' => $w1->id,
            'service_request_id' => $sr1->id,
            'transaction_type' => 'job_earning',
            'debit' => 0.00,
            'credit' => 2000.00,
            'running_balance' => 2000.00,
            'description' => "Earning for {$sr1->sr_number} - {$jobAc->title}",
            'reference_number' => $sr1->sr_number,
            'created_by_user_id' => $superAdmin->id,
            'created_at' => Carbon::yesterday()->setHour(14),
        ]);

        // Request 2: In Progress Today
        $sr2 = ServiceRequest::create([
            'sr_number' => 'SR-260902-0002',
            'customer_id' => $c2->id,
            'city_id' => $lahore->id,
            'category_id' => $jobElec->category_id,
            'sub_category_id' => $jobElec->sub_category_id,
            'service_job_id' => $jobElec->id,
            'service_address' => $c2->address,
            'customer_complaint' => 'Main breaker tripping frequently when AC is turned on.',
            'preferred_date' => Carbon::today(),
            'preferred_time' => '02:00 PM - 04:00 PM',
            'request_source' => 'whatsapp',
            'status' => 'in_progress',
            'current_worker_id' => $w2->id,
            'customer_price' => $jobElec->default_customer_price, // 3000
            'worker_payment' => $jobElec->default_worker_payment, // 2400
            'commission_amount' => $jobElec->default_commission,  // 600
            'is_ledger_posted' => false,
            'created_by_user_id' => $opsUser->id,
            'assigned_at' => Carbon::today()->setHour(10),
            'created_at' => Carbon::today()->setHour(9),
        ]);

        ServiceRequestStatusHistory::create([
            'service_request_id' => $sr2->id,
            'from_status' => 'new',
            'to_status' => 'in_progress',
            'worker_id' => $w2->id,
            'notes' => 'Worker arrived at location and diagnosing breaker box.',
            'created_at' => Carbon::today()->setHour(14),
        ]);

        WorkerAssignment::create([
            'service_request_id' => $sr2->id,
            'worker_id' => $w2->id,
            'assigned_by_user_id' => $opsUser->id,
            'status' => 'accepted',
            'assigned_at' => Carbon::today()->setHour(10),
            'responded_at' => Carbon::today()->setHour(10)->addMinutes(5),
        ]);

        // 9. Brands & Free Installation Partners
        $brandsData = [
            ['name' => 'Hisense', 'code' => 'HISENSE', 'rate' => 2500, 'contact' => 'Imran Khan', 'phone' => '03008881122', 'email' => 'service@hisense.pk'],
            ['name' => 'Gree', 'code' => 'GREE', 'rate' => 2800, 'contact' => 'Adnan Siddiqui', 'phone' => '03009993344', 'email' => 'warranty@gree.pk'],
            ['name' => 'Kenwood', 'code' => 'KENWOOD', 'rate' => 2600, 'contact' => 'Tariq Javed', 'phone' => '03007775566', 'email' => 'support@kenwood.pk'],
            ['name' => 'Xiaomi', 'code' => 'XIAOMI', 'rate' => 2200, 'contact' => 'Smart Tech Team', 'phone' => '03006664433', 'email' => 'service@xiaomi.pk'],
            ['name' => 'Midea', 'code' => 'MIDEA', 'rate' => 2400, 'contact' => 'Operations Head', 'phone' => '03005553322', 'email' => 'info@midea.pk'],
            ['name' => 'EcoStar', 'code' => 'ECOSTAR', 'rate' => 2300, 'contact' => 'Customer Care', 'phone' => '03004442211', 'email' => 'claims@ecostar.pk'],
            ['name' => 'TCL', 'code' => 'TCL', 'rate' => 2500, 'contact' => 'Warranty Desk', 'phone' => '03003331100', 'email' => 'service@tcl.pk'],
        ];

        $createdBrands = [];
        foreach ($brandsData as $b) {
            $brand = \App\Models\Brand::create([
                'name' => $b['name'],
                'code' => $b['code'],
                'contact_person' => $b['contact'],
                'phone' => $b['phone'],
                'email' => $b['email'],
                'default_installation_rate' => $b['rate'],
                'status' => 'active',
            ]);
            $createdBrands[$b['name']] = $brand;
        }

        // Brand Users
        $hisenseBrand = $createdBrands['Hisense'];
        $greeBrand = $createdBrands['Gree'];

        User::create([
            'name' => 'Hisense Service Portal',
            'email' => 'brand@hisense.com',
            'password' => Hash::make('password123'),
            'role' => 'brand',
            'brand_id' => $hisenseBrand->id,
            'phone' => '03008881122',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Gree Warranty Portal',
            'email' => 'brand@gree.com',
            'password' => Hash::make('password123'),
            'role' => 'brand',
            'brand_id' => $greeBrand->id,
            'phone' => '03009993344',
            'is_active' => true,
        ]);

        // Sample Brand Free Installation Job (Verified with Duration & Brand Ledger)
        $brandJob1 = ServiceRequest::create([
            'sr_number' => 'SR-260902-0004',
            'customer_id' => $c1->id,
            'city_id' => $lahore->id,
            'category_id' => $jobAc->category_id,
            'sub_category_id' => $jobAc->sub_category_id,
            'service_job_id' => $jobAc->id,
            'service_address' => $c1->address,
            'customer_complaint' => 'Brand New AC Free Installation',
            'request_source' => 'brand',
            'status' => 'verified',
            'is_free_installation' => true,
            'is_brand_job' => true,
            'brand_id' => $hisenseBrand->id,
            'model_number' => 'HS-INV-1.5T-COOL',
            'serial_number' => 'HS9876543210',
            'quantity' => 1,
            'brand_rate' => 2500.00,
            'customer_price' => 0.00, // Customer pays Rs. 0
            'worker_payment' => 2000.00, // Worker earned Rs. 2,000
            'commission_amount' => 500.00,
            'is_ledger_posted' => true,
            'is_brand_ledger_posted' => true,
            'current_worker_id' => $w1->id,
            'assigned_at' => Carbon::yesterday()->setHour(10),
            'started_at' => Carbon::yesterday()->setHour(10)->addMinutes(30),
            'completed_at' => Carbon::yesterday()->setHour(11)->addMinutes(45),
            'duration_minutes' => 75, // 1 hr 15 mins
            'verified_at' => Carbon::yesterday()->setHour(12),
            'verified_by_user_id' => $superAdmin->id,
            'created_by_user_id' => $opsUser->id,
            'created_at' => Carbon::yesterday()->setHour(9),
        ]);

        // Brand Ledger Entry for Hisense
        \App\Models\BrandLedger::create([
            'transaction_number' => 'BTX-2026-00001',
            'brand_id' => $hisenseBrand->id,
            'service_request_id' => $brandJob1->id,
            'transaction_type' => 'job_billing',
            'debit' => 2500.00,
            'credit' => 0.00,
            'running_balance' => 2500.00,
            'description' => "Installation Billing: {$brandJob1->sr_number} (Model: HS-INV-1.5T-COOL)",
            'reference_number' => $brandJob1->sr_number,
            'created_by_user_id' => $superAdmin->id,
            'created_at' => Carbon::yesterday()->setHour(12),
        ]);
    }
}
