<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\SubCategory;
use App\Models\Worker;
use App\Models\WorkerBankDetail;
use App\Models\WorkerCategory;
use App\Models\WorkerDocument;
use App\Models\WorkerReference;
use App\Models\WorkerServiceArea;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $query = Worker::with(['primaryCity', 'categories', 'workerCategories.subCategory']);

        // Search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cnic_number', 'like', "%{$search}%")
                    ->orWhere('worker_code', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($cityId = $request->query('city_id')) {
            $query->where(function ($q) use ($cityId) {
                $q->where('primary_city_id', $cityId)
                    ->orWhereHas('serviceAreas', fn($sq) => $sq->where('city_id', $cityId));
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->whereHas('workerCategories', fn($q) => $q->where('category_id', $categoryId));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($availability = $request->query('availability')) {
            $query->where('availability', $availability);
        }

        $workers = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $cities = City::where('status', 'active')->get();
        $categories = Category::where('status', 'active')->get();

        return view('workers.index', compact('workers', 'cities', 'categories'));
    }

    public function create()
    {
        $cities = City::with('areas')->where('status', 'active')->get();
        $categories = Category::with('subCategories')->where('status', 'active')->get();

        return view('workers.create', compact('cities', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'cnic_number' => ['required', 'string', 'max:30', 'unique:workers,cnic_number'],
            'phone' => ['required', 'string', 'max:30', 'unique:workers,phone'],
            'email' => ['nullable', 'email', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'primary_city_id' => ['required', 'exists:cities,id'],
            'additional_cities' => ['nullable', 'array'],
            'additional_cities.*' => ['exists:cities,id'],
            
            // Professional
            'primary_category_id' => ['required', 'exists:categories,id'],
            'primary_sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'additional_categories' => ['nullable', 'array'],
            'additional_categories.*' => ['exists:categories,id'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'skill_level' => ['required', Rule::in(['basic', 'intermediate', 'experienced', 'expert'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'availability' => ['required', Rule::in(['available', 'busy', 'on_leave', 'unavailable'])],
            'password' => ['nullable', 'string', 'min:6'],

            // Bank
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_title' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:50'],

            // References
            'references' => ['nullable', 'array', 'max:3'],
            'references.*.name' => ['nullable', 'string', 'max:150'],
            'references.*.phone' => ['nullable', 'string', 'max:30'],
            'references.*.relationship' => ['nullable', 'string', 'max:100'],
            'references.*.notes' => ['nullable', 'string', 'max:255'],

            // Files
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'cnic_front' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'cnic_back' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Generate unique sequential Worker Code
            $maxId = Worker::withTrashed()->max('id') ?? 0;
            $workerCode = 'SW-W-' . ($maxId + 1001);

            // Handle Photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('uploads/workers/photos', 'public');
            }

            $worker = Worker::create([
                'worker_code' => $workerCode,
                'name' => $validated['name'],
                'cnic_number' => $validated['cnic_number'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'primary_city_id' => $validated['primary_city_id'],
                'primary_category_id' => $validated['primary_category_id'],
                'primary_sub_category_id' => $validated['primary_sub_category_id'] ?? null,
                'experience_years' => $validated['experience_years'],
                'skill_level' => $validated['skill_level'],
                'status' => $validated['status'],
                'availability' => $validated['availability'],
                'password' => Hash::make($validated['password'] ?? '123456'),
                'visible_password' => $validated['password'] ?? '123456',
                'photo_path' => $photoPath,
            ]);

            // Save Primary Category
            WorkerCategory::create([
                'worker_id' => $worker->id,
                'category_id' => $validated['primary_category_id'],
                'sub_category_id' => $validated['primary_sub_category_id'] ?? null,
                'is_primary' => true,
            ]);

            // Additional Categories
            if (!empty($validated['additional_categories'])) {
                foreach ($validated['additional_categories'] as $addCatId) {
                    if ($addCatId != $validated['primary_category_id']) {
                        WorkerCategory::firstOrCreate([
                            'worker_id' => $worker->id,
                            'category_id' => $addCatId,
                            'sub_category_id' => null,
                        ], ['is_primary' => false]);
                    }
                }
            }

            // Save Service Areas (Primary + Additional)
            WorkerServiceArea::create([
                'worker_id' => $worker->id,
                'city_id' => $validated['primary_city_id'],
                'is_primary' => true,
            ]);

            if (!empty($validated['additional_cities'])) {
                foreach ($validated['additional_cities'] as $addCityId) {
                    if ($addCityId != $validated['primary_city_id']) {
                        WorkerServiceArea::firstOrCreate([
                            'worker_id' => $worker->id,
                            'city_id' => $addCityId,
                        ], ['is_primary' => false]);
                    }
                }
            }

            // Save Bank Details
            if (!empty($validated['bank_name']) || !empty($validated['account_number'])) {
                WorkerBankDetail::create([
                    'worker_id' => $worker->id,
                    'bank_name' => $validated['bank_name'] ?? null,
                    'account_title' => $validated['account_title'] ?? null,
                    'account_number' => $validated['account_number'] ?? null,
                    'iban' => $validated['iban'] ?? null,
                    'verification_status' => 'not_verified',
                ]);
            }

            // Save References
            if (!empty($validated['references'])) {
                foreach ($validated['references'] as $ref) {
                    if (!empty($ref['name']) && !empty($ref['phone'])) {
                        WorkerReference::create([
                            'worker_id' => $worker->id,
                            'name' => $ref['name'],
                            'phone' => $ref['phone'],
                            'relationship' => $ref['relationship'] ?? null,
                            'notes' => $ref['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Save CNIC Documents
            if ($request->hasFile('cnic_front')) {
                $file = $request->file('cnic_front');
                $path = $file->store('uploads/workers/cnic', 'public');
                WorkerDocument::create([
                    'worker_id' => $worker->id,
                    'document_type' => 'cnic_front',
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size_bytes' => $file->getSize(),
                    'is_verified' => false,
                ]);
            }

            if ($request->hasFile('cnic_back')) {
                $file = $request->file('cnic_back');
                $path = $file->store('uploads/workers/cnic', 'public');
                WorkerDocument::create([
                    'worker_id' => $worker->id,
                    'document_type' => 'cnic_back',
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size_bytes' => $file->getSize(),
                    'is_verified' => false,
                ]);
            }

            AuditLogService::log(
                action: 'created',
                module: 'workers',
                recordId: $worker->id,
                description: "Worker registered: {$worker->name} ({$worker->worker_code})"
            );

            return redirect()->route('workers.show', $worker)->with('success', 'Worker registered successfully.');
        });
    }

    public function show(Worker $worker)
    {
        $worker->load([
            'primaryCity',
            'cities',
            'categories',
            'workerCategories.subCategory',
            'bankDetail',
            'references',
            'documents',
            'serviceRequests' => fn($q) => $q->with(['customer', 'serviceJob', 'city'])->orderBy('created_at', 'desc'),
            'ledgerTransactions' => fn($q) => $q->orderBy('created_at', 'desc')->limit(50),
        ]);

        $totalJobs = $worker->serviceRequests()->count();
        $completedJobs = $worker->serviceRequests()->whereIn('status', ['completed', 'verified'])->count();
        $cancelledJobs = $worker->serviceRequests()->where('status', 'cancelled')->count();
        $currentBalance = $worker->getLedgerBalance();

        return view('workers.show', compact('worker', 'totalJobs', 'completedJobs', 'cancelledJobs', 'currentBalance'));
    }

    public function edit(Worker $worker)
    {
        $worker->load(['workerCategories', 'serviceAreas', 'bankDetail', 'references']);
        $cities = City::where('status', 'active')->get();
        $categories = Category::with('subCategories')->where('status', 'active')->get();

        return view('workers.edit', compact('worker', 'cities', 'categories'));
    }

    public function update(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'cnic_number' => ['required', 'string', 'max:30', Rule::unique('workers', 'cnic_number')->ignore($worker->id)],
            'phone' => ['required', 'string', 'max:30', Rule::unique('workers', 'phone')->ignore($worker->id)],
            'email' => ['nullable', 'email', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'primary_city_id' => ['required', 'exists:cities,id'],
            'additional_cities' => ['nullable', 'array'],
            'additional_cities.*' => ['exists:cities,id'],

            // Professional
            'primary_category_id' => ['nullable', 'exists:categories,id'],
            'primary_sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'additional_categories' => ['nullable', 'array'],
            'additional_categories.*' => ['exists:categories,id'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'skill_level' => ['required', Rule::in(['basic', 'intermediate', 'experienced', 'expert'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'availability' => ['required', Rule::in(['available', 'busy', 'on_leave', 'unavailable'])],
            'password' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],

            // Bank
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_title' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:50'],

            // References
            'references' => ['nullable', 'array', 'max:3'],
            'references.*.name' => ['nullable', 'string', 'max:150'],
            'references.*.phone' => ['nullable', 'string', 'max:30'],
            'references.*.relationship' => ['nullable', 'string', 'max:100'],
            'references.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($request, $worker, $validated) {
            $updateData = [
                'name' => $validated['name'],
                'cnic_number' => $validated['cnic_number'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'primary_city_id' => $validated['primary_city_id'],
                'primary_category_id' => $validated['primary_category_id'] ?? $worker->primary_category_id,
                'primary_sub_category_id' => $validated['primary_sub_category_id'] ?? $worker->primary_sub_category_id,
                'experience_years' => $validated['experience_years'],
                'skill_level' => $validated['skill_level'],
                'status' => $validated['status'],
                'availability' => $validated['availability'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
                $updateData['visible_password'] = $validated['password'];
            }

            if ($request->hasFile('photo')) {
                $updateData['photo_path'] = $request->file('photo')->store('uploads/workers/photos', 'public');
            }

            $worker->update($updateData);

            // Synchronize Service Areas (Primary + Additional Cities)
            WorkerServiceArea::where('worker_id', $worker->id)->delete();
            WorkerServiceArea::create([
                'worker_id' => $worker->id,
                'city_id' => $validated['primary_city_id'],
                'is_primary' => true,
            ]);

            if (!empty($validated['additional_cities'])) {
                foreach ($validated['additional_cities'] as $addCityId) {
                    if ($addCityId != $validated['primary_city_id']) {
                        WorkerServiceArea::firstOrCreate([
                            'worker_id' => $worker->id,
                            'city_id' => $addCityId,
                        ], ['is_primary' => false]);
                    }
                }
            }

            // Synchronize Categories
            if (!empty($validated['primary_category_id'])) {
                WorkerCategory::where('worker_id', $worker->id)->delete();
                WorkerCategory::create([
                    'worker_id' => $worker->id,
                    'category_id' => $validated['primary_category_id'],
                    'sub_category_id' => $validated['primary_sub_category_id'] ?? null,
                    'is_primary' => true,
                ]);

                if (!empty($validated['additional_categories'])) {
                    foreach ($validated['additional_categories'] as $addCatId) {
                        if ($addCatId != $validated['primary_category_id']) {
                            WorkerCategory::firstOrCreate([
                                'worker_id' => $worker->id,
                                'category_id' => $addCatId,
                                'sub_category_id' => null,
                            ], ['is_primary' => false]);
                        }
                    }
                }
            }

            // Save Bank Details
            if (!empty($validated['bank_name']) || !empty($validated['account_number']) || !empty($validated['iban'])) {
                WorkerBankDetail::updateOrCreate(
                    ['worker_id' => $worker->id],
                    [
                        'bank_name' => $validated['bank_name'] ?? null,
                        'account_title' => $validated['account_title'] ?? null,
                        'account_number' => $validated['account_number'] ?? null,
                        'iban' => $validated['iban'] ?? null,
                    ]
                );
            }

            // Save References
            if (isset($validated['references'])) {
                WorkerReference::where('worker_id', $worker->id)->delete();
                foreach ($validated['references'] as $ref) {
                    if (!empty($ref['name']) && !empty($ref['phone'])) {
                        WorkerReference::create([
                            'worker_id' => $worker->id,
                            'name' => $ref['name'],
                            'phone' => $ref['phone'],
                            'relationship' => $ref['relationship'] ?? null,
                            'notes' => $ref['notes'] ?? null,
                        ]);
                    }
                }
            }

            AuditLogService::log(
                action: 'updated',
                module: 'workers',
                recordId: $worker->id,
                description: "Updated worker profile for {$worker->name}"
            );

            return redirect()->route('workers.show', $worker)->with('success', 'Worker profile updated successfully.');
        });
    }

    public function updateStatus(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
            'availability' => ['nullable', Rule::in(['available', 'busy', 'on_leave', 'unavailable'])],
        ]);

        if (isset($validated['status'])) {
            $worker->status = $validated['status'];
        }
        if (isset($validated['availability'])) {
            $worker->availability = $validated['availability'];
        }
        $worker->save();

        AuditLogService::log(
            action: 'updated',
            module: 'workers',
            recordId: $worker->id,
            description: "Changed status to {$worker->status}, availability to {$worker->availability}"
        );

        return back()->with('success', 'Worker status updated successfully.');
    }

    public function quickPasswordUpdate(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:4'],
        ]);

        $worker->update([
            'password' => Hash::make($validated['password']),
            'visible_password' => $validated['password'],
        ]);

        AuditLogService::log(
            action: 'password_reset',
            module: 'workers',
            recordId: $worker->id,
            description: "Admin changed PIN/password for worker {$worker->name} ({$worker->worker_code})"
        );

        return back()->with('success', "PIN/Password for {$worker->name} has been updated successfully.");
    }
}
