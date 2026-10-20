<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::withCount(['serviceRequests', 'serviceJobs']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $brands = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:brands,code',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            // Optional brand user account creation
            'create_user' => 'nullable|boolean',
            'user_name' => 'required_if:create_user,1|nullable|string|max:255',
            'user_email' => 'required_if:create_user,1|nullable|email|max:255|unique:users,email',
            'user_password' => 'required_if:create_user,1|nullable|string|min:6',
        ]);

        $brand = Brand::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'default_installation_rate' => 0.00,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if (!empty($validated['create_user'])) {
            User::create([
                'name' => $validated['user_name'],
                'email' => $validated['user_email'],
                'password' => Hash::make($validated['user_password']),
                'visible_password' => $validated['user_password'],
                'role' => 'brand',
                'brand_id' => $brand->id,
                'is_active' => true,
            ]);
        }

        AuditLogService::log(
            action: 'created',
            module: 'brands',
            recordId: $brand->id,
            description: "Created brand {$brand->name} ({$brand->code})"
        );

        return redirect()->route('brands.index')->with('success', "Brand '{$brand->name}' created successfully.");
    }

    public function show(Brand $brand)
    {
        $brand->loadCount('serviceRequests');
        $brand->load(['serviceJobs.category', 'serviceJobs.subCategory']);
        $ledgerBalance = $brand->getLedgerBalance();
        
        $recentJobs = $brand->serviceRequests()
            ->with(['customer', 'city', 'currentWorker'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $brandUsers = User::where('brand_id', $brand->id)->get();

        return view('brands.show', compact('brand', 'ledgerBalance', 'recentJobs', 'brandUsers'));
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('brands')->ignore($brand->id)],
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $brand->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogService::log(
            action: 'updated',
            module: 'brands',
            recordId: $brand->id,
            description: "Updated brand {$brand->name}"
        );

        return redirect()->route('brands.show', $brand)->with('success', "Brand '{$brand->name}' updated successfully.");
    }

    public function destroy(Brand $brand)
    {
        if ($brand->serviceRequests()->exists()) {
            return back()->with('error', "Cannot delete brand '{$brand->name}' because it has associated service jobs.");
        }

        $brandName = $brand->name;
        $brand->delete();

        AuditLogService::log(
            action: 'deleted',
            module: 'brands',
            recordId: $brand->id,
            description: "Deleted brand {$brandName}"
        );

        return redirect()->route('brands.index')->with('success', "Brand '{$brandName}' deleted.");
    }
}
