<?php

namespace App\Http\Controllers;

use App\Models\BrandLedger;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\JobProof;
use App\Models\LedgerTransaction;
use App\Models\ServiceJob;
use App\Models\ServiceRequest;
use App\Models\SubCategory;
use App\Models\Worker;
use App\Services\AuditLogService;
use App\Services\BrandLedgerService;
use App\Services\ServiceRequestService;
use App\Services\WorkerLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function __construct(
        protected ServiceRequestService $srService,
        protected WorkerLedgerService $ledgerService,
        protected BrandLedgerService $brandLedgerService
    ) {}

    public function index(Request $request)
    {
        $query = ServiceRequest::with(['customer', 'city', 'category', 'serviceJob', 'currentWorker']);

        // Search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sr_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('phone', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($cityId = $request->query('city_id')) {
            $query->where('city_id', $cityId);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($jobId = $request->query('job_id')) {
            $query->where('service_job_id', $jobId);
        }

        if ($workerId = $request->query('worker_id')) {
            $query->where('current_worker_id', $workerId);
        }

        if ($startDate = $request->query('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $serviceRequests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $cities = City::where('status', 'active')->get();
        $categories = Category::where('status', 'active')->get();
        $jobs = ServiceJob::where('status', 'active')->get();
        $workers = Worker::where('status', 'active')->get();

        return view('service_requests.index', compact(
            'serviceRequests',
            'cities',
            'categories',
            'jobs',
            'workers'
        ));
    }

    public function create()
    {
        $cities = City::with('areas')->where('status', 'active')->get();
        $categories = Category::with('subCategories')->where('status', 'active')->get();
        $jobs = ServiceJob::with('prices')->where('status', 'active')->get();
        $brands = \App\Models\Brand::where('status', 'active')->orderBy('name')->get();

        return view('service_requests.create', compact('cities', 'categories', 'jobs', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Customer Info (Either existing ID or new details)
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30'],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:150'],
            'customer_email' => ['nullable', 'email', 'max:150'],

            // Brand & Free Installation & After Sales
            'is_free_installation' => ['nullable', 'boolean'],
            'is_brand_job' => ['nullable', 'boolean'],
            'job_nature' => ['nullable', 'string', 'in:standard,free_installation,brand_after_sale'],
            'warranty_coverage' => ['nullable', 'string', 'in:out_of_warranty,in_warranty,partial_warranty'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'model_number' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'brand_rate' => ['nullable', 'numeric', 'min:0'],
            'additional_jobs' => ['nullable', 'string'],
            'service_value' => ['nullable', 'numeric', 'min:0'],
            'parts_value' => ['nullable', 'numeric', 'min:0'],

            // Job Details
            'city_id' => ['required', 'exists:cities,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'service_job_id' => ['required', 'exists:service_jobs,id'],
            'service_address' => ['required', 'string', 'max:500'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'customer_complaint' => ['nullable', 'string'],
            'special_instructions' => ['nullable', 'string'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'request_source' => ['required', Rule::in(['phone', 'whatsapp', 'website', 'brand', 'worker', 'other'])],
            
            // Financial Override (optional)
            'customer_price' => ['nullable', 'numeric', 'min:0'],
            'worker_payment' => ['nullable', 'numeric', 'min:0'],
            'commission_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Resolve Customer
            if (!empty($validated['customer_id'])) {
                $customer = Customer::findOrFail($validated['customer_id']);
            } else {
                // Find by phone or create new
                $customer = Customer::where('phone', $validated['customer_phone'])->first();
                if (!$customer) {
                    $code = 'SW-C-' . (Customer::withTrashed()->count() + 1001);
                    $customer = Customer::create([
                        'customer_code' => $code,
                        'name' => $validated['customer_name'],
                        'phone' => $validated['customer_phone'],
                        'email' => $validated['customer_email'] ?? null,
                        'city_id' => $validated['city_id'],
                        'address' => $validated['service_address'],
                    ]);

                    CustomerAddress::create([
                        'customer_id' => $customer->id,
                        'city_id' => $validated['city_id'],
                        'area_id' => $validated['area_id'] ?? null,
                        'title' => 'Primary',
                        'address_line' => $validated['service_address'],
                        'location_notes' => $validated['location_notes'] ?? null,
                        'is_default' => true,
                    ]);
                }
            }

            // 2. Resolve Pricing & Job Nature
            $job = ServiceJob::findOrFail($validated['service_job_id']);
            $defaultPricing = $job->getPriceForCity($validated['city_id']);

            $jobNature = $validated['job_nature'] ?? ($job->is_free_installation ? 'free_installation' : ($job->is_brand_job ? 'brand_after_sale' : 'standard'));
            $isFreeInstallation = ($jobNature === 'free_installation') || !empty($validated['is_free_installation']);
            $isBrandJob = ($jobNature === 'brand_after_sale' || $isFreeInstallation || !empty($validated['brand_id']) || $job->is_brand_job);
            $brandId = $validated['brand_id'] ?? ($isBrandJob ? $job->brand_id : null);
            $brandRate = (float)($validated['brand_rate'] ?? ($isFreeInstallation ? ($job->brand_rate ?: 0) : 0));
            $serviceValue = (float)($validated['service_value'] ?? 0);
            $partsValue = (float)($validated['parts_value'] ?? 0);

            if ($isFreeInstallation) {
                $custPrice = 0.00;
                $wrkPayment = $validated['worker_payment'] ?? $defaultPricing['worker_payment'];
                $commission = max(0, $brandRate - $wrkPayment);
            } elseif ($serviceValue > 0 || $partsValue > 0) {
                // Calculated from service value (20% commission) and parts value (10% commission)
                $calculatedCommission = ($serviceValue * 0.20) + ($partsValue * 0.10);
                $commission = $validated['commission_amount'] ?? $calculatedCommission;
                $custPrice = $validated['customer_price'] ?? ($serviceValue + $partsValue);
                $wrkPayment = $validated['worker_payment'] ?? max(0, ($custPrice + $brandRate) - $commission);
            } else {
                $custPrice = $validated['customer_price'] ?? $defaultPricing['customer_price'];
                $wrkPayment = $validated['worker_payment'] ?? $defaultPricing['worker_payment'];
                $commission = $validated['commission_amount'] ?? ($custPrice - $wrkPayment);
                if ($commission < 0) $commission = 0;
            }

            // 3. Create Service Request
            $srNumber = $this->srService->generateSrNumber();

            $serviceRequest = ServiceRequest::create([
                'sr_number' => $srNumber,
                'customer_id' => $customer->id,
                'city_id' => $validated['city_id'],
                'area_id' => $validated['area_id'] ?? null,
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'] ?? null,
                'service_job_id' => $job->id,
                'service_address' => $validated['service_address'],
                'location_notes' => $validated['location_notes'] ?? null,
                'customer_complaint' => $validated['customer_complaint'] ?? null,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'additional_jobs' => $validated['additional_jobs'] ?? null,
                'service_value' => $serviceValue,
                'parts_value' => $partsValue,
                'job_nature' => $jobNature,
                'warranty_coverage' => $validated['warranty_coverage'] ?? null,
                'preferred_date' => $validated['preferred_date'] ?? null,
                'preferred_time' => $validated['preferred_time'] ?? null,
                'request_source' => $validated['request_source'],
                'status' => 'new',
                'is_free_installation' => $isFreeInstallation,
                'is_brand_job' => $isBrandJob,
                'brand_id' => $brandId,
                'model_number' => $validated['model_number'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'quantity' => $validated['quantity'] ?? 1,
                'brand_rate' => $brandRate,
                'customer_price' => $custPrice,
                'worker_payment' => $wrkPayment,
                'commission_amount' => $commission,
                'created_by_user_id' => Auth::id(),
            ]);

            // 4. Initial Status History
            $this->srService->logStatusChange(
                $serviceRequest,
                null,
                'new',
                Auth::id(),
                null,
                "Service Request created via {$validated['request_source']}"
            );

            AuditLogService::log(
                action: 'created',
                module: 'service_requests',
                recordId: $serviceRequest->id,
                description: "Created {$serviceRequest->sr_number} for {$customer->name} ({$job->title})"
            );

            return redirect()->route('service-requests.show', $serviceRequest)->with('success', 'Service request created successfully.');
        });
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load([
            'customer',
            'city',
            'area',
            'category',
            'subCategory',
            'serviceJob',
            'brand',
            'currentWorker.primaryCity',
            'createdBy',
            'verifiedBy',
            'statusHistory.changedBy',
            'statusHistory.worker',
            'assignments.worker',
            'assignments.assignedBy',
            'assignmentHistory.previousWorker',
            'assignmentHistory.newWorker',
            'assignmentHistory.changedBy',
            'proofs',
            'ledgerTransactions',
            'brandLedgers',
        ]);

        // Get matching suitable workers for dispatch modal
        $suitableWorkers = $this->srService->getSuitableWorkers($serviceRequest);

        return view('service_requests.show', compact('serviceRequest', 'suitableWorkers'));
    }

    public function assignWorker(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'exists:workers,id'],
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        $this->srService->assignWorker($serviceRequest, $worker, Auth::id());

        return back()->with('success', "Assigned to worker {$worker->name} successfully.");
    }

    public function reassignWorker(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'exists:workers,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        $this->srService->assignWorker($serviceRequest, $worker, Auth::id());

        return back()->with('success', "Reassigned to worker {$worker->name} successfully.");
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'new', 'pending_assignment', 'assigned', 'accepted',
                'on_the_way', 'arrived', 'in_progress', 'completed', 'verified', 'cancelled'
            ])],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->srService->updateStatus(
                $serviceRequest,
                $validated['status'],
                Auth::id(),
                null,
                $validated['notes'] ?? null
            );
            return back()->with('success', "Status updated to " . strtoupper(str_replace('_', ' ', $validated['status'])) . " successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    public function edit(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['customer', 'city', 'category', 'subCategory', 'serviceJob', 'currentWorker', 'brand']);

        $cities = City::where('status', 'active')->get();
        $categories = Category::with('subCategories')->where('status', 'active')->get();
        $subCategories = SubCategory::where('category_id', $serviceRequest->category_id)->where('status', 'active')->get();
        $jobs = ServiceJob::where('category_id', $serviceRequest->category_id)->where('status', 'active')->get();
        $brands = \App\Models\Brand::where('status', 'active')->orderBy('name')->get();
        $workers = Worker::where('status', 'active')->get();

        return view('service_requests.edit', compact(
            'serviceRequest',
            'cities',
            'categories',
            'subCategories',
            'jobs',
            'brands',
            'workers'
        ));
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'city_id' => ['required', 'exists:cities,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'service_job_id' => ['required', 'exists:service_jobs,id'],
            
            // Brand & Job details
            'is_free_installation' => ['nullable', 'boolean'],
            'is_brand_job' => ['nullable', 'boolean'],
            'job_nature' => ['nullable', 'string', 'in:standard,free_installation,brand_after_sale'],
            'warranty_coverage' => ['nullable', 'string', 'in:out_of_warranty,in_warranty,partial_warranty'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'model_number' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'brand_rate' => ['nullable', 'numeric', 'min:0'],
            'additional_jobs' => ['nullable', 'string'],
            'service_value' => ['nullable', 'numeric', 'min:0'],
            'parts_value' => ['nullable', 'numeric', 'min:0'],
            
            'service_address' => ['required', 'string', 'max:500'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'assigned_at' => ['nullable', 'date'],
            'verified_at' => ['nullable', 'date'],
            'request_source' => ['required', Rule::in(['phone', 'whatsapp', 'website', 'brand', 'worker', 'other'])],
            'status' => ['required', Rule::in([
                'new', 'pending_assignment', 'assigned', 'accepted',
                'on_the_way', 'arrived', 'in_progress', 'completed', 'verified', 'cancelled'
            ])],
            'current_worker_id' => ['nullable', 'exists:workers,id'],
            'customer_price' => ['required', 'numeric', 'min:0'],
            'worker_payment' => ['required', 'numeric', 'min:0'],
            'commission_amount' => ['required', 'numeric', 'min:0'],
            'customer_complaint' => ['nullable', 'string'],
            'special_instructions' => ['nullable', 'string'],
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated, $serviceRequest) {
            // 1. Update customer profile info if changed
            if ($serviceRequest->customer) {
                $serviceRequest->customer->update([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? $serviceRequest->customer->email,
                ]);
            }

            $oldStatus = $serviceRequest->status;
            $newStatus = $validated['status'];
            $oldWorkerId = $serviceRequest->current_worker_id;
            $newWorkerId = $validated['current_worker_id'] ?? null;

            // 2. Handle worker assignment change if changed
            if ($newWorkerId && $newWorkerId != $oldWorkerId) {
                $worker = Worker::findOrFail($newWorkerId);
                $this->srService->assignWorker($serviceRequest, $worker, Auth::id());
            } elseif (!$newWorkerId && $oldWorkerId) {
                // Unassign worker
                $serviceRequest->update(['current_worker_id' => null]);
                \App\Models\WorkerAssignment::where('service_request_id', $serviceRequest->id)
                    ->where('worker_id', $oldWorkerId)
                    ->whereIn('status', ['pending', 'accepted'])
                    ->update(['status' => 'cancelled']);
            }

            $jobNature = $validated['job_nature'] ?? (!empty($validated['is_free_installation']) ? 'free_installation' : 'standard');
            $isFreeInstallation = ($jobNature === 'free_installation') || !empty($validated['is_free_installation']);
            $isBrandJob = ($jobNature === 'brand_after_sale' || $isFreeInstallation || !empty($validated['brand_id']));

            $assignedAt = !empty($validated['assigned_at']) ? Carbon::parse($validated['assigned_at']) : null;
            $startedAt = !empty($validated['started_at']) ? Carbon::parse($validated['started_at']) : null;
            $completedAt = !empty($validated['completed_at']) ? Carbon::parse($validated['completed_at']) : null;
            $verifiedAt = !empty($validated['verified_at']) ? Carbon::parse($validated['verified_at']) : null;

            // Calculate duration based on effective start and completion
            $effectiveStart = $assignedAt ?? $startedAt ?? $serviceRequest->assigned_at ?? $serviceRequest->started_at ?? $serviceRequest->created_at;
            $effectiveEnd = $completedAt ?? $verifiedAt ?? $serviceRequest->completed_at ?? $serviceRequest->verified_at;

            $durationMinutes = $serviceRequest->duration_minutes;
            if ($effectiveStart && $effectiveEnd) {
                $startCarbon = Carbon::parse($effectiveStart);
                $endCarbon = Carbon::parse($effectiveEnd);
                if ($endCarbon->gte($startCarbon)) {
                    $durationMinutes = (int)$startCarbon->diffInMinutes($endCarbon);
                }
            }

            // 3. Update Service Request record
            $serviceRequest->update([
                'city_id' => $validated['city_id'],
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'] ?? null,
                'service_job_id' => $validated['service_job_id'],
                'is_free_installation' => $isFreeInstallation,
                'is_brand_job' => $isBrandJob,
                'job_nature' => $jobNature,
                'warranty_coverage' => $validated['warranty_coverage'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'model_number' => $validated['model_number'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'quantity' => $validated['quantity'] ?? 1,
                'brand_rate' => $validated['brand_rate'] ?? 0,
                'service_address' => $validated['service_address'],
                'location_notes' => $validated['location_notes'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'preferred_date' => $validated['preferred_date'] ?? null,
                'preferred_time' => $validated['preferred_time'] ?? null,
                'assigned_at' => $assignedAt ?? $serviceRequest->assigned_at,
                'started_at' => $startedAt ?? $serviceRequest->started_at,
                'completed_at' => $completedAt ?? $serviceRequest->completed_at,
                'verified_at' => $verifiedAt ?? $serviceRequest->verified_at,
                'duration_minutes' => $durationMinutes,
                'request_source' => $validated['request_source'],
                'customer_price' => $validated['customer_price'],
                'worker_payment' => $validated['worker_payment'],
                'commission_amount' => $validated['commission_amount'],
                'service_value' => $validated['service_value'] ?? 0,
                'parts_value' => $validated['parts_value'] ?? 0,
                'customer_complaint' => $validated['customer_complaint'] ?? null,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'additional_jobs' => $validated['additional_jobs'] ?? null,
                'cancellation_reason' => $validated['cancellation_reason'] ?? null,
            ]);

            // 4. Handle status transitions and ledger consequences
            if ($oldStatus !== $newStatus) {
                $this->srService->updateStatus(
                    $serviceRequest,
                    $newStatus,
                    Auth::id(),
                    null,
                    "Status manually updated by Super Admin/Operator" . (!empty($validated['cancellation_reason']) ? ": {$validated['cancellation_reason']}" : "")
                );
            }

            // 5. Synchronize ledger transactions with updated values
            if (in_array($newStatus, ['completed', 'verified'])) {
                $this->ledgerService->syncJobEarning($serviceRequest, Auth::id());
                $this->brandLedgerService->syncJobBilling($serviceRequest, Auth::id());
            }

            AuditLogService::log(
                action: 'updated',
                module: 'service_requests',
                recordId: $serviceRequest->id,
                description: "Super Admin updated Service Request {$serviceRequest->sr_number}"
            );

            return redirect()->route('service-requests.show', $serviceRequest)->with('success', "Service Request {$serviceRequest->sr_number} updated successfully.");
        });
    }

    /**
     * Upload Installation / Proof Images from Backend.
     */
    public function uploadProof(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'proof_type' => ['required', 'string', 'in:indoor_serial,outdoor_serial,warranty_card,indoor_unit,outdoor_unit,indoor_box_serial,outdoor_box_serial,leakage_test,serial_barcode,model_plate,completion_proof,customer_signature,general_proof,other'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $uploadedFiles = [];
        if ($request->hasFile('images')) {
            $uploadedFiles = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $uploadedFiles = [$request->file('image')];
        }

        if (empty($uploadedFiles)) {
            return back()->with('error', 'Please select at least one image file to upload.');
        }

        $proofTypeLabels = [
            'indoor_serial' => 'a. Indoor Serial',
            'outdoor_serial' => 'b. Outdoor Serial',
            'warranty_card' => 'c. Filled Warranty Card',
            'indoor_unit' => 'd. Indoor Installed Unit',
            'outdoor_unit' => 'e. Outdoor Installed Unit',
            'indoor_box_serial' => 'f. Indoor Box Serial Number',
            'outdoor_box_serial' => 'g. Outdoor Box Serial Number',
            'leakage_test' => 'h. Leakage Test',
            'serial_barcode' => 'Serial Number Barcode',
            'model_plate' => 'Model Rating Plate',
            'completion_proof' => 'Completed Work / Installation Proof',
            'customer_signature' => 'Customer Acceptance',
            'general_proof' => 'General Service Photo',
            'other' => 'Additional Proof',
        ];

        $defaultCaption = $request->caption ?: ($proofTypeLabels[$request->proof_type] ?? 'Installation Proof');
        $workerId = $serviceRequest->current_worker_id ?: (\App\Models\Worker::first()?->id ?: 1);

        foreach ($uploadedFiles as $file) {
            $path = $file->store("proofs/{$serviceRequest->sr_number}", 'public');

            JobProof::create([
                'service_request_id' => $serviceRequest->id,
                'worker_id' => $workerId,
                'proof_type' => $request->proof_type,
                'image_path' => $path,
                'caption' => $defaultCaption,
                'created_at' => now(),
            ]);
        }

        AuditLogService::log(
            action: 'uploaded_proof',
            module: 'service_requests',
            recordId: $serviceRequest->id,
            description: "Uploaded " . count($uploadedFiles) . " proof photo(s) for {$serviceRequest->sr_number}"
        );

        return back()->with('success', count($uploadedFiles) . ' proof photo(s) uploaded successfully.');
    }

    /**
     * Delete a Job Proof Image.
     */
    public function deleteProof(JobProof $proof)
    {
        $sr = $proof->serviceRequest;
        
        if ($proof->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($proof->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($proof->image_path);
        }

        $proof->delete();

        AuditLogService::log(
            action: 'deleted_proof',
            module: 'service_requests',
            recordId: $sr?->id,
            description: "Deleted proof photo #{$proof->id} for {$sr?->sr_number}"
        );

        return back()->with('success', 'Proof photo deleted successfully.');
    }

    public function destroy(ServiceRequest $serviceRequest)
    {
        $srNumber = $serviceRequest->sr_number;
        $this->srService->deleteServiceRequest($serviceRequest, 'Deleted by Super Admin', Auth::id());

        return redirect()->route('service-requests.index')->with('success', "Service Request {$srNumber} and all financial ledger transactions safely reversed and deleted.");
    }
}
