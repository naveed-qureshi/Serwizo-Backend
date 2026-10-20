<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\ServiceJob;
use App\Models\ServiceJobPrice;
use App\Models\SubCategory;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceJobController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceJob::with(['category', 'subCategory', 'brand']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($catId = $request->query('category_id')) {
            $query->where('category_id', $catId);
        }

        if ($subCatId = $request->query('sub_category_id')) {
            $query->where('sub_category_id', $subCatId);
        }

        $status = $request->has('status') ? $request->query('status') : 'active';
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $jobs = $query->orderBy('title')->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->get();
        $subCategories = SubCategory::where('status', 'active')->get();

        return view('jobs.index', compact('jobs', 'categories', 'subCategories'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        $cities = City::where('status', 'active')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        return view('jobs.create', compact('categories', 'cities', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50', 'unique:service_jobs,code'],
            'description' => ['nullable', 'string'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:5'],
            'default_customer_price' => ['nullable', 'numeric', 'min:0'],
            'default_worker_payment' => ['required', 'numeric', 'min:0'],
            'default_commission' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            
            // Promotional Pricing parameters
            'is_promotional' => ['nullable', 'boolean'],
            'promotional_customer_price' => ['nullable', 'numeric', 'min:0'],
            'promotional_worker_payment' => ['nullable', 'numeric', 'min:0'],
            'promotional_commission' => ['nullable', 'numeric', 'min:0'],

            // Brand job parameters
            'is_brand_job' => ['nullable', 'boolean'],
            'brand_id' => ['nullable', 'required_if:is_brand_job,1', 'exists:brands,id'],
            'is_free_installation' => ['nullable', 'boolean'],
            'brand_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $isBrandJob = !empty($validated['is_brand_job']) || !empty($validated['brand_id']);
        $isFreeInstallation = $isBrandJob;
        $brandId = $isBrandJob ? ($validated['brand_id'] ?? null) : null;
        $brandRate = $isBrandJob ? (float)($validated['brand_rate'] ?? 0) : 0.00;
        $customerPrice = $isBrandJob ? 0.00 : (float)($validated['default_customer_price'] ?? 0);
        $workerPayment = (float)$validated['default_worker_payment'];

        // Calculate commission: Brand Rate - Worker Payment for Brand Jobs, Customer Price - Worker Payment for Cash Jobs
        $commission = $isBrandJob ? max(0, $brandRate - $workerPayment) : max(0, $customerPrice - $workerPayment);
        if (isset($validated['default_commission']) && $validated['default_commission'] !== null && (float)$validated['default_commission'] > 0) {
            $commission = (float)$validated['default_commission'];
        }

        $isPromotional = !empty($validated['is_promotional']);
        $promotionalCustomerPrice = $isPromotional && isset($validated['promotional_customer_price']) ? (float)$validated['promotional_customer_price'] : null;
        $promotionalWorkerPayment = $isPromotional && isset($validated['promotional_worker_payment']) ? (float)$validated['promotional_worker_payment'] : null;
        $promotionalCommission = null;
        if ($isPromotional && $promotionalCustomerPrice !== null && $promotionalWorkerPayment !== null) {
            $promotionalCommission = isset($validated['promotional_commission']) && $validated['promotional_commission'] !== null && (float)$validated['promotional_commission'] > 0
                ? (float)$validated['promotional_commission']
                : max(0, $promotionalCustomerPrice - $promotionalWorkerPayment);
        }

        $job = ServiceJob::create([
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'title' => $validated['title'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'default_customer_price' => $customerPrice,
            'default_worker_payment' => $workerPayment,
            'default_commission' => $commission,
            'is_promotional' => $isPromotional,
            'promotional_customer_price' => $promotionalCustomerPrice,
            'promotional_worker_payment' => $promotionalWorkerPayment,
            'promotional_commission' => $promotionalCommission,
            'status' => $validated['status'],
            'is_brand_job' => $isBrandJob,
            'brand_id' => $brandId,
            'is_free_installation' => $isFreeInstallation,
            'brand_rate' => $brandRate,
        ]);

        AuditLogService::log(
            action: 'created',
            module: 'jobs',
            recordId: $job->id,
            description: "Created job service: {$job->title} (Rate: Rs. " . ($isBrandJob ? $brandRate : $customerPrice) . ", Wage: Rs. {$workerPayment}, Commission: Rs. {$commission})"
        );

        return redirect()->route('jobs.index')->with('success', 'Job created successfully.');
    }

    public function edit(ServiceJob $job)
    {
        $categories = Category::where('status', 'active')->get();
        $subCategories = SubCategory::where('category_id', $job->category_id)->where('status', 'active')->get();
        $cities = City::where('status', 'active')->get();
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        $job->load('prices');

        return view('jobs.edit', compact('job', 'categories', 'subCategories', 'cities', 'brands'));
    }

    public function update(Request $request, ServiceJob $job)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('service_jobs', 'code')->ignore($job->id)],
            'description' => ['nullable', 'string'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:5'],
            'default_customer_price' => ['nullable', 'numeric', 'min:0'],
            'default_worker_payment' => ['required', 'numeric', 'min:0'],
            'default_commission' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            
            // Promotional Pricing parameters
            'is_promotional' => ['nullable', 'boolean'],
            'promotional_customer_price' => ['nullable', 'numeric', 'min:0'],
            'promotional_worker_payment' => ['nullable', 'numeric', 'min:0'],
            'promotional_commission' => ['nullable', 'numeric', 'min:0'],

            // Brand job parameters
            'is_brand_job' => ['nullable', 'boolean'],
            'brand_id' => ['nullable', 'required_if:is_brand_job,1', 'exists:brands,id'],
            'is_free_installation' => ['nullable', 'boolean'],
            'brand_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $isBrandJob = !empty($validated['is_brand_job']) || !empty($validated['brand_id']);
        $isFreeInstallation = $isBrandJob;
        $brandId = $isBrandJob ? ($validated['brand_id'] ?? null) : null;
        $brandRate = $isBrandJob ? (float)($validated['brand_rate'] ?? 0) : 0.00;
        $customerPrice = $isBrandJob ? 0.00 : (float)($validated['default_customer_price'] ?? 0);
        $workerPayment = (float)$validated['default_worker_payment'];

        $commission = $isBrandJob ? max(0, $brandRate - $workerPayment) : max(0, $customerPrice - $workerPayment);
        if (isset($validated['default_commission']) && $validated['default_commission'] !== null && (float)$validated['default_commission'] > 0) {
            $commission = (float)$validated['default_commission'];
        }

        $isPromotional = !empty($validated['is_promotional']);
        $promotionalCustomerPrice = $isPromotional && isset($validated['promotional_customer_price']) ? (float)$validated['promotional_customer_price'] : null;
        $promotionalWorkerPayment = $isPromotional && isset($validated['promotional_worker_payment']) ? (float)$validated['promotional_worker_payment'] : null;
        $promotionalCommission = null;
        if ($isPromotional && $promotionalCustomerPrice !== null && $promotionalWorkerPayment !== null) {
            $promotionalCommission = isset($validated['promotional_commission']) && $validated['promotional_commission'] !== null && (float)$validated['promotional_commission'] > 0
                ? (float)$validated['promotional_commission']
                : max(0, $promotionalCustomerPrice - $promotionalWorkerPayment);
        }

        $job->update([
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'title' => $validated['title'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'default_customer_price' => $customerPrice,
            'default_worker_payment' => $workerPayment,
            'default_commission' => $commission,
            'is_promotional' => $isPromotional,
            'promotional_customer_price' => $promotionalCustomerPrice,
            'promotional_worker_payment' => $promotionalWorkerPayment,
            'promotional_commission' => $promotionalCommission,
            'status' => $validated['status'],
            'is_brand_job' => $isBrandJob,
            'brand_id' => $brandId,
            'is_free_installation' => $isFreeInstallation,
            'brand_rate' => $brandRate,
        ]);

        // Synchronize updated rates on non-finalized service requests referencing this job
        \App\Models\ServiceRequest::where('service_job_id', $job->id)
            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->update([
                'brand_id' => $brandId,
                'is_brand_job' => $isBrandJob,
                'is_free_installation' => $isBrandJob,
                'brand_rate' => $brandRate,
                'customer_price' => $customerPrice,
                'worker_payment' => $workerPayment,
                'commission_amount' => $commission,
            ]);

        AuditLogService::log(
            action: 'updated',
            module: 'jobs',
            recordId: $job->id,
            description: "Updated job service: {$job->title} (Rate: Rs. " . ($isBrandJob ? $brandRate : $customerPrice) . ", Wage: Rs. {$workerPayment}, Commission: Rs. {$commission})"
        );

        return redirect()->route('jobs.index')->with('success', 'Job updated successfully.');
    }

    public function getByCategory(Request $request)
    {
        $categoryId = $request->query('category_id');
        $subCategoryId = $request->query('sub_category_id');
        $cityId = $request->query('city_id');

        $query = ServiceJob::with('brand')->where('status', 'active');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($subCategoryId) {
            $query->where('sub_category_id', $subCategoryId);
        }

        $jobs = $query->get()->map(function ($job) use ($cityId) {
            $pricing = $job->getPriceForCity($cityId ? (int)$cityId : null);
            return [
                'id' => $job->id,
                'title' => $job->title,
                'code' => $job->code,
                'is_brand_job' => (bool)$job->is_brand_job,
                'brand_id' => $job->brand_id,
                'brand_name' => $job->brand?->name,
                'is_free_installation' => (bool)$job->is_free_installation,
                'brand_rate' => (float)$job->brand_rate,
                'customer_price' => (float)$pricing['customer_price'],
                'worker_payment' => (float)$pricing['worker_payment'],
                'commission' => (float)$pricing['commission'],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }
}
