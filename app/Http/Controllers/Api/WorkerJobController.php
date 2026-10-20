<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobProof;
use App\Models\ServiceRequest;
use App\Models\WorkerAssignment;
use App\Services\ServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkerJobController extends Controller
{
    public function __construct(
        protected ServiceRequestService $srService
    ) {}

    public function index(Request $request)
    {
        $worker = $request->user();
        $filter = $request->query('filter', 'all'); // pending, active, history, all

        $query = ServiceRequest::with(['customer', 'city', 'category', 'subCategory', 'serviceJob', 'brand', 'proofs'])
            ->where('current_worker_id', $worker->id);

        if ($filter === 'pending') {
            $query->where('status', 'assigned');
        } elseif ($filter === 'active') {
            $query->whereIn('status', ['assigned', 'accepted', 'on_the_way', 'arrived', 'in_progress']);
        } elseif ($filter === 'history') {
            $query->whereIn('status', ['completed', 'verified', 'cancelled']);
        }

        $jobs = $query->orderBy('created_at', 'desc')->get();

        $items = $jobs->map(function ($job) use ($worker) {
            $statusMap = [
                'new' => 'ASSIGNED',
                'pending_assignment' => 'ASSIGNED',
                'assigned' => 'ASSIGNED',
                'accepted' => 'ASSIGNED',
                'on_the_way' => 'IN_PROGRESS',
                'arrived' => 'IN_PROGRESS',
                'in_progress' => 'IN_PROGRESS',
                'completed' => 'COMPLETED',
                'verified' => 'COMPLETED',
                'cancelled' => 'MISSED',
                'rejected' => 'MISSED',
            ];

            return [
                'id' => $job->sr_number,
                'database_id' => $job->id,
                'sr_number' => $job->sr_number,
                'title' => $job->serviceJob?->title ?? $job->category?->title ?? 'General Service',
                'category' => $job->category?->title,
                'brandId' => $job->brand_id ? ('b_' . strtolower($job->brand?->name ?? 'brand')) : 'b_general',
                'brandName' => $job->brand?->name ?? 'General',
                'serviceName' => $job->serviceJob?->title ?? $job->category?->title ?? 'General Service',
                'jobType' => $job->is_free_installation ? 'INSTALLATION' : 'REPAIR',
                'isFreeInstallation' => (bool)$job->is_free_installation,
                'is_free_installation' => (bool)$job->is_free_installation,
                'brandRate' => (float)$job->brand_rate,
                'brand_rate' => (float)$job->brand_rate,
                'workerWage' => (float)$job->worker_payment,
                'worker_wage' => (float)$job->worker_payment,
                'customerTotal' => (float)$job->customer_price,
                'customer_total' => (float)$job->customer_price,
                'quantity' => (int)($job->quantity ?? 1),
                'customerName' => $job->customer?->name ?? 'Customer',
                'customer_name' => $job->customer?->name ?? 'Customer',
                'customerMobile' => $job->customer?->phone ?? '',
                'customer_phone' => $job->customer?->phone ?? '',
                'customerAltPhone' => $job->customer?->alt_phone,
                'customer_alt_phone' => $job->customer?->alt_phone,
                'city' => $job->city?->name ?? 'Lahore',
                'area' => $job->city?->name ?? 'General Area',
                'address' => $job->service_address ?? '',
                'service_address' => $job->service_address ?? '',
                'latitude' => $job->latitude ? (float)$job->latitude : null,
                'longitude' => $job->longitude ? (float)$job->longitude : null,
                'preferredDate' => $job->preferred_date?->format('d F Y') ?? now()->format('d F Y'),
                'preferred_date' => $job->preferred_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'preferredTime' => $job->preferred_time ?? '09:00 AM - 05:00 PM',
                'preferred_time' => $job->preferred_time ?? '09:00 AM - 05:00 PM',
                'instructions' => $job->special_instructions ?? $job->customer_complaint ?? '',
                'customerComplaint' => $job->customer_complaint,
                'status' => $statusMap[$job->status] ?? 'ASSIGNED',
                'workerId' => $worker->worker_code,
                'worker_id' => $worker->worker_code,
                'workerName' => $worker->name,
                'worker_name' => $worker->name,
                'modelNumber' => $job->model_number,
                'serialNumber' => $job->serial_number,
                'checklist' => [],
                'photos' => $job->proofs->map(fn($p) => [
                    'id' => 'p_' . $p->id,
                    'categoryId' => 'cat_' . $p->proof_type,
                    'categoryTitle' => ucfirst(str_replace('_', ' ', $p->proof_type)),
                    'dataUrl' => $p->image_url,
                    'timestamp' => $p->created_at->toIso8601String(),
                    'jobId' => $job->sr_number,
                    'workerId' => $worker->worker_code,
                ])->values()->all(),
                'workNotes' => null,
                'jobNature' => $job->job_nature ?? ($job->is_free_installation ? 'free_installation' : 'cash_job'),
                'job_nature' => $job->job_nature ?? ($job->is_free_installation ? 'free_installation' : 'cash_job'),
                'warrantyCoverage' => $job->warranty_coverage,
                'warranty_coverage' => $job->warranty_coverage,
                'additionalJobs' => $job->additional_jobs,
                'additional_jobs' => $job->additional_jobs,
                'items' => $job->items,
                'serviceValue' => (float)($job->service_value ?? 0),
                'service_value' => (float)($job->service_value ?? 0),
                'partsValue' => (float)($job->parts_value ?? 0),
                'parts_value' => (float)($job->parts_value ?? 0),
                'startedAt' => $job->started_at?->toIso8601String(),
                'completedAt' => $job->completed_at?->toIso8601String(),
                'durationMinutes' => $job->duration_minutes,
                'createdAt' => $job->created_at->toIso8601String(),
                'created_at' => $job->created_at->toIso8601String(),
                'updatedAt' => $job->updated_at->toIso8601String(),
                'syncStatus' => 'SYNCED',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Jobs retrieved successfully.',
            'data' => $items,
        ]);
    }

    protected function findJobForWorker($worker, $id): ServiceRequest
    {
        $cleanId = trim((string)$id);

        $job = ServiceRequest::where(function ($q) use ($cleanId) {
                if (is_numeric($cleanId)) {
                    $q->where('id', (int)$cleanId)->orWhere('sr_number', $cleanId);
                } else {
                    $q->where('sr_number', $cleanId)->orWhere('id', $cleanId);
                }
            })
            ->where(function ($q) use ($worker) {
                $q->where('current_worker_id', $worker->id)
                  ->orWhereNull('current_worker_id');
            })
            ->first();

        if ($job) {
            if (!$job->current_worker_id) {
                $job->current_worker_id = $worker->id;
                $job->save();
            }
            return $job;
        }

        // If job was initiated on-site in the app and synced directly on completion, auto-create it now
        $now = now();
        $cityId = $worker->primary_city_id ?? \App\Models\City::first()?->id ?? 1;
        $categoryId = $worker->categories->first()?->id ?? \App\Models\Category::first()?->id ?? 1;
        $serviceJob = \App\Models\ServiceJob::where('category_id', $categoryId)->where('status', 'active')->first() 
            ?? \App\Models\ServiceJob::where('status', 'active')->first();
        $brand = $serviceJob?->brand ?? \App\Models\Brand::where('status', 'active')->first();
        
        $customer = \App\Models\Customer::firstOrCreate(
            ['phone' => '03000000000'],
            [
                'customer_code' => 'SW-C-' . (\App\Models\Customer::withTrashed()->count() + 1001),
                'name' => 'App Customer',
                'address' => $worker->address ?? 'On-Site Location',
                'city_id' => $cityId,
                'status' => 'active',
            ]
        );

        $isBrandJob = (bool)($serviceJob?->is_brand_job || $brand);
        $brandRate = (float)($serviceJob?->brand_rate ?: 2800.00);
        $workerPayment = (float)($serviceJob?->default_worker_payment ?: 2000.00);
        $customerPrice = $isBrandJob ? 0.00 : (float)($serviceJob?->default_customer_price ?: 2800.00);
        $commission = max(0, ($isBrandJob ? $brandRate : $customerPrice) - $workerPayment);

        $job = ServiceRequest::create([
            'sr_number' => (preg_match('/^SR-\d{2}-\d{6}$/i', $cleanId)) ? strtoupper($cleanId) : $this->srService->generateSrNumber(),
            'customer_id' => $customer->id,
            'city_id' => $cityId,
            'category_id' => $categoryId,
            'service_job_id' => $serviceJob?->id ?? 1,
            'service_address' => $worker->address ?? 'On-Site Customer Address',
            'customer_complaint' => 'On-site installation completed via mobile app',
            'request_source' => 'app',
            'status' => 'in_progress',
            'current_worker_id' => $worker->id,
            'assigned_at' => $now,
            'started_at' => $now,
            'is_free_installation' => $isBrandJob,
            'is_brand_job' => $isBrandJob,
            'brand_id' => $brand?->id,
            'brand_rate' => $brandRate,
            'customer_price' => $customerPrice,
            'worker_payment' => $workerPayment,
            'commission_amount' => $commission,
        ]);

        WorkerAssignment::create([
            'service_request_id' => $job->id,
            'worker_id' => $worker->id,
            'status' => 'accepted',
            'assigned_at' => $now,
            'responded_at' => $now,
        ]);

        return $job;
    }

    public function show(Request $request, $id)
    {
        $worker = $request->user();
        $job = ServiceRequest::with(['customer', 'city', 'category', 'subCategory', 'serviceJob', 'proofs'])
            ->where('current_worker_id', $worker->id)
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', (int)$id)->orWhere('sr_number', (string)$id);
                } else {
                    $q->where('sr_number', (string)$id)->orWhere('id', $id);
                }
            })
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Job detail retrieved.',
            'data' => [
                'id' => $job->id,
                'sr_number' => $job->sr_number,
                'job_title' => $job->serviceJob?->title,
                'job_description' => $job->serviceJob?->description,
                'category' => $job->category?->title,
                'sub_category' => $job->subCategory?->title,
                'status' => $job->status,
                'customer' => [
                    'name' => $job->customer?->name,
                    'phone' => $job->customer?->phone,
                    'alt_phone' => $job->customer?->alt_phone,
                ],
                'location' => [
                    'city' => $job->city?->name,
                    'service_address' => $job->service_address,
                    'location_notes' => $job->location_notes,
                    'latitude' => $job->latitude,
                    'longitude' => $job->longitude,
                ],
                'scheduling' => [
                    'preferred_date' => $job->preferred_date?->format('Y-m-d'),
                    'preferred_time' => $job->preferred_time,
                ],
                'financial' => [
                    'worker_wage' => (float)$job->worker_payment,
                    'customer_total' => (float)$job->customer_price,
                ],
                'items' => $job->items,
                'additional_jobs' => $job->additional_jobs,
                'customer_complaint' => $job->customer_complaint,
                'special_instructions' => $job->special_instructions,
                'proofs' => $job->proofs->map(fn($p) => [
                    'id' => $p->id,
                    'type' => $p->proof_type,
                    'image_url' => $p->image_url,
                    'caption' => $p->caption,
                    'created_at' => $p->created_at->toIso8601String(),
                ]),
                'created_at' => $job->created_at->toIso8601String(),
            ],
        ]);
    }

    public function accept(Request $request, $id)
    {
        $worker = $request->user();
        $job = $this->findJobForWorker($worker, $id);

        if ($job->status !== 'assigned') {
            return response()->json([
                'success' => false,
                'message' => "Job cannot be accepted in current status: {$job->status}",
            ], 422);
        }

        $this->srService->updateStatus($job, 'accepted', null, $worker->id, 'Worker accepted the job via app.');

        return response()->json([
            'success' => true,
            'message' => 'Job accepted successfully.',
            'data' => ['status' => 'accepted'],
        ]);
    }

    public function reject(Request $request, $id)
    {
        $worker = $request->user();
        $job = $this->findJobForWorker($worker, $id);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if (!in_array($job->status, ['assigned', 'accepted'])) {
            return response()->json([
                'success' => false,
                'message' => 'Job cannot be rejected in current status.',
            ], 422);
        }

        // Update assignment record
        WorkerAssignment::where('service_request_id', $job->id)
            ->where('worker_id', $worker->id)
            ->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['reason'],
                'responded_at' => now(),
            ]);

        // Unassign and reset to pending_assignment
        $job->update([
            'current_worker_id' => null,
            'status' => 'pending_assignment',
        ]);

        $this->srService->logStatusChange(
            $job,
            $job->status,
            'pending_assignment',
            null,
            $worker->id,
            "Worker rejected job: {$validated['reason']}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Job rejected and returned to dispatch queue.',
            'data' => ['status' => 'pending_assignment'],
        ]);
    }

    public function updateProgressStatus(Request $request, $id)
    {
        $worker = $request->user();
        $job = $this->findJobForWorker($worker, $id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['on_the_way', 'arrived', 'in_progress'])],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->srService->updateStatus($job, $validated['status'], null, $worker->id, $validated['notes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Job status updated to ' . $validated['status'],
            'data' => ['status' => $validated['status']],
        ]);
    }

    protected function saveProofImage(ServiceRequest $job, $worker, string $proofType, $imageInput, ?string $caption = null, ?float $lat = null, ?float $lng = null): ?JobProof
    {
        $cleanType = strtolower(str_replace('cat_', '', $proofType));
        $validTypes = [
            'indoor_serial', 'outdoor_serial', 'warranty_card', 'indoor_unit', 'outdoor_unit',
            'indoor_box_serial', 'outdoor_box_serial', 'leakage_test',
            'before_work', 'after_work', 'installation', 'completion',
            'serial_barcode', 'model_plate', 'customer_signature', 'general_proof', 'other'
        ];
        if (!in_array($cleanType, $validTypes)) {
            $cleanType = 'other';
        }

        $path = null;
        if ($imageInput instanceof \Illuminate\Http\UploadedFile) {
            $path = $imageInput->store('uploads/jobs/proofs', 'public');
        } elseif (is_string($imageInput) && !empty($imageInput)) {
            $base64Data = $imageInput;
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $ext = strtolower($matches[1]);
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }
            if ($ext === 'jpeg') $ext = 'jpg';

            $binaryData = base64_decode($base64Data);
            if ($binaryData !== false && strlen($binaryData) > 0) {
                $filename = 'uploads/jobs/proofs/' . uniqid('proof_', true) . '.' . $ext;
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $binaryData);
                $path = $filename;
            }
        }

        if (!$path) {
            return null;
        }

        return JobProof::create([
            'service_request_id' => $job->id,
            'worker_id' => $worker->id,
            'proof_type' => $cleanType,
            'image_path' => $path,
            'caption' => $caption,
            'latitude' => $lat,
            'longitude' => $lng,
            'created_at' => now(),
        ]);
    }

    public function uploadProof(Request $request, $id)
    {
        $worker = $request->user();
        $job = $this->findJobForWorker($worker, $id);

        $proofType = $request->input('proof_type') ?? $request->input('categoryId') ?? 'general_proof';
        $caption = $request->input('caption');
        $lat = $request->input('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->input('longitude') ? (float)$request->input('longitude') : null;

        $imageInput = $request->file('image') 
            ?? $request->input('image_base64') 
            ?? $request->input('data_url') 
            ?? $request->input('dataUrl') 
            ?? $request->input('image');

        if (!$imageInput) {
            return response()->json([
                'success' => false,
                'message' => 'An image file or base64 data is required.',
            ], 422);
        }

        $proof = $this->saveProofImage($job, $worker, $proofType, $imageInput, $caption, $lat, $lng);

        if (!$proof) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process and store the image.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Job proof uploaded successfully.',
            'data' => [
                'id' => $proof->id,
                'proof_type' => $proof->proof_type,
                'image_url' => $proof->image_url,
            ],
        ]);
    }

    public function start(Request $request, $id)
    {
        $worker = $request->user();
        $job = $this->findJobForWorker($worker, $id);

        $this->srService->updateStatus($job, 'in_progress', null, $worker->id, 'Worker started the job.');

        return response()->json([
            'success' => true,
            'message' => 'Job started successfully.',
            'data' => [
                'status' => 'in_progress',
                'started_at' => $job->fresh()->started_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Worker initiates a job directly from the mobile app (e.g. Free Installation or on-site request)
     */
    public function initiate(Request $request)
    {
        $worker = $request->user();

        // Enforce: Only workers belonging to the Appliances category have the right to generate free installation jobs
        $isAppliancesWorker = $worker->categories()->whereRaw('LOWER(title) LIKE ?', ['%appliance%'])->exists();
        if (!$isAppliancesWorker) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only technicians in the Appliances category are authorized to generate free installation jobs on the mobile app. All other jobs must be created via the Serwizo dispatch backend.',
            ], 403);
        }

        $validated = $request->validate([
            'sr_number' => 'nullable|string|max:50',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_alt_phone' => 'nullable|string|max:50',
            'service_address' => 'required|string|max:500',
            'service_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'city_id' => 'nullable|exists:cities,id',
            'category_id' => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'service_job_id' => 'nullable|exists:service_jobs,id',
            
            // Brand and Installation specifics
            'is_free_installation' => 'nullable|boolean',
            'is_brand_job' => 'nullable|boolean',
            'brand_id' => 'nullable',
            'brand_name' => 'nullable|string|max:100',
            'model_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'quantity' => 'nullable|integer|min:1',
            'brand_rate' => 'nullable|numeric|min:0',
            'worker_payment' => 'nullable|numeric|min:0',
            'worker_wage' => 'nullable|numeric|min:0',
            
            'customer_complaint' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
            'start_immediately' => 'nullable|boolean',
        ]);

        // If job with this sr_number already exists, return it (idempotency)
        if (!empty($validated['sr_number'])) {
            $existing = ServiceRequest::where('sr_number', $validated['sr_number'])->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Job already exists.',
                    'data' => [
                        'id' => $existing->sr_number,
                        'database_id' => $existing->id,
                        'sr_number' => $existing->sr_number,
                        'status' => $existing->status,
                        'customer_name' => $existing->customer?->name ?? 'Customer',
                        'customer_phone' => $existing->customer?->phone ?? '',
                        'service_address' => $existing->service_address,
                        'is_free_installation' => (bool)$existing->is_free_installation,
                        'model_number' => $existing->model_number,
                        'serial_number' => $existing->serial_number,
                        'worker_wage' => (float)$existing->worker_payment,
                        'started_at' => $existing->started_at?->toIso8601String(),
                        'created_at' => $existing->created_at->toIso8601String(),
                    ],
                ], 200);
            }
        }

        $cityId = $validated['city_id'] ?? $worker->primary_city_id ?? \App\Models\City::first()?->id;
        $categoryId = $validated['category_id'] ?? $worker->categories->first()?->id ?? \App\Models\Category::first()?->id;

        // Resolve Brand (Strictly required for Worker App self-initiation)
        $brandId = null;
        $brandObj = null;
        if (!empty($validated['brand_id']) && is_numeric($validated['brand_id'])) {
            $brandId = (int)$validated['brand_id'];
            $brandObj = \App\Models\Brand::find($brandId);
        } elseif (!empty($validated['brand_name'])) {
            $brandObj = \App\Models\Brand::where('name', 'like', '%' . $validated['brand_name'] . '%')
                ->orWhere('code', 'like', '%' . $validated['brand_name'] . '%')
                ->first();
            $brandId = $brandObj?->id;
        } elseif (!empty($validated['brand_id'])) {
            $cleanCode = str_replace('b_', '', strtolower((string)$validated['brand_id']));
            $brandObj = \App\Models\Brand::where('code', 'like', $cleanCode)
                ->orWhere('name', 'like', "%{$cleanCode}%")
                ->first();
            $brandId = $brandObj?->id;
        }

        $jobTitle = $validated['job_title'] ?? $validated['service_name'] ?? null;
        if (!$brandObj && $jobTitle) {
            $allBrands = \App\Models\Brand::where('status', 'active')->get();
            foreach ($allBrands as $b) {
                if (stripos($jobTitle, $b->name) !== false || stripos($jobTitle, $b->code) !== false) {
                    $brandObj = $b;
                    $brandId = $b->id;
                    break;
                }
            }
        }

        if (!$brandObj) {
            $brandObj = \App\Models\Brand::where('status', 'active')->first();
            $brandId = $brandObj?->id;
        }

        if (!$brandObj) {
            return response()->json([
                'success' => false,
                'message' => 'Technicians can only initiate Free Installation jobs associated with an authorized Brand partner.',
            ], 422);
        }

        $isFreeInstallation = true;
        $isBrandJob = true;

        if (!$jobTitle && $brandObj) {
            $product = $validated['product_name'] ?? 'Inverter AC';
            $jobTitle = "Free Installation {$brandObj->name} {$product}";
        }

        $serviceJob = null;
        if (!empty($validated['service_job_id'])) {
            $serviceJob = \App\Models\ServiceJob::find($validated['service_job_id']);
        }

        if (!$serviceJob && $jobTitle) {
            $serviceJob = \App\Models\ServiceJob::where('title', 'like', "%{$jobTitle}%")->first();
        }

        if (!$serviceJob && $brandId) {
            $serviceJob = \App\Models\ServiceJob::where('brand_id', $brandId)->first();
        }

        if (!$serviceJob) {
            $serviceJob = \App\Models\ServiceJob::where('category_id', $categoryId)->first() ?? \App\Models\ServiceJob::first();
        }

        $serviceJobId = $serviceJob?->id ?? 1;

        // Find or create customer
        $customer = \App\Models\Customer::firstOrCreate(
            ['phone' => $validated['customer_phone']],
            [
                'customer_code' => 'SW-C-' . (\App\Models\Customer::withTrashed()->count() + 1001),
                'name' => $validated['customer_name'],
                'alt_phone' => $validated['customer_alt_phone'] ?? null,
                'address' => $validated['service_address'],
                'city_id' => $cityId,
                'status' => 'active',
            ]
        );
        
        $brandRate = (float)($validated['brand_rate'] ?? 0);
        if ($brandRate <= 0) {
            $brandRate = (float)($serviceJob?->brand_rate ?: 2800.00);
        }

        $workerWage = isset($validated['worker_payment']) && (float)$validated['worker_payment'] > 0
            ? (float)$validated['worker_payment']
            : (isset($validated['worker_wage']) && (float)$validated['worker_wage'] > 0 
                ? (float)$validated['worker_wage'] 
                : (float)($serviceJob?->default_worker_payment ?: 2000.00));

        $customerPrice = 0.00; // Customer always pays Rs. 0 for Brand Free Installation
        $commission = max(0, $brandRate - $workerWage);

        $status = !empty($validated['start_immediately']) ? 'in_progress' : 'assigned';
        $now = now();
        $clientSr = !empty($validated['sr_number']) ? trim($validated['sr_number']) : null;
        if ($clientSr && preg_match('/^SR-\d{2}-\d{6}$/i', $clientSr)) {
            $srNumber = strtoupper($clientSr);
        } else {
            $srNumber = $this->srService->generateSrNumber();
        }

        $complaint = $validated['customer_complaint'] 
            ?? $validated['description'] 
            ?? ($jobTitle ? "{$jobTitle} (Brand Free Installation)" : 'On-Site Brand Installation Request');

        $sr = ServiceRequest::create([
            'sr_number' => $srNumber,
            'customer_id' => $customer->id,
            'city_id' => $cityId,
            'category_id' => $categoryId,
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'service_job_id' => $serviceJobId,
            'service_address' => $validated['service_address'],
            'location_notes' => null,
            'customer_complaint' => $complaint,
            'special_instructions' => $validated['special_instructions'] ?? null,
            'request_source' => 'app',
            'status' => $status,
            'current_worker_id' => $worker->id,
            'assigned_at' => $now,
            'started_at' => ($status === 'in_progress') ? $now : null,
            'is_free_installation' => true,
            'is_brand_job' => true,
            'job_nature' => 'free_installation',
            'brand_id' => $brandId,
            'model_number' => $validated['model_number'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'quantity' => $validated['quantity'] ?? 1,
            'brand_rate' => $brandRate,
            'customer_price' => 0.00,
            'worker_payment' => $workerWage,
            'commission_amount' => $commission,
            'created_by_user_id' => null,
        ]);

        WorkerAssignment::create([
            'service_request_id' => $sr->id,
            'worker_id' => $worker->id,
            'assigned_by_user_id' => null,
            'status' => ($status === 'in_progress') ? 'accepted' : 'pending',
            'assigned_at' => $now,
            'responded_at' => ($status === 'in_progress') ? $now : null,
        ]);

        $this->srService->logStatusChange(
            $sr,
            null,
            $status,
            null,
            $worker->id,
            "Job initiated on-site by worker {$worker->name}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Job created and initiated successfully.',
            'data' => [
                'id' => $sr->sr_number,
                'database_id' => $sr->id,
                'sr_number' => $sr->sr_number,
                'status' => $sr->status,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'service_address' => $sr->service_address,
                'is_free_installation' => $sr->is_free_installation,
                'model_number' => $sr->model_number,
                'serial_number' => $sr->serial_number,
                'worker_wage' => (float)$sr->worker_payment,
                'started_at' => $sr->started_at?->toIso8601String(),
                'created_at' => $sr->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function completeJob(Request $request, $id)
    {
        $worker = $request->user();
        $job = $this->findJobForWorker($worker, $id);

        // Process any attached photos sent with the completion payload
        if ($request->has('photos') && is_array($request->input('photos'))) {
            foreach ($request->input('photos') as $p) {
                $type = $p['categoryId'] ?? $p['category_id'] ?? $p['proof_type'] ?? 'general_proof';
                $data = $p['dataUrl'] ?? $p['data_url'] ?? $p['image'] ?? null;
                $caption = $p['caption'] ?? $p['categoryTitle'] ?? null;
                if ($data) {
                    $this->saveProofImage($job, $worker, $type, $data, $caption);
                }
            }
        }

        if ($job->status === 'completed' || $job->status === 'verified') {
            return response()->json([
                'success' => true,
                'message' => 'Job photos attached and marked as completed.',
                'data' => [
                    'status' => $job->status,
                    'earned_amount' => (float)$job->worker_payment,
                ],
            ]);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'photos' => ['nullable', 'array'],
        ]);

        $this->srService->updateStatus(
            $job,
            'completed',
            null,
            $worker->id,
            $validated['notes'] ?? 'Job marked completed by worker via mobile app.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Job marked completed successfully. Worker earning is posted to your ledger.',
            'data' => [
                'status' => 'completed',
                'earned_amount' => (float)$job->worker_payment,
            ],
        ]);
    }
}
