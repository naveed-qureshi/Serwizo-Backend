<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['city'])->withCount([
            'serviceRequests',
            'serviceRequests as completed_requests_count' => fn($q) => $q->whereIn('status', ['completed', 'verified'])
        ]);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        if ($cityId = $request->query('city_id')) {
            $query->where('city_id', $cityId);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $cities = City::where('status', 'active')->get();

        return view('customers.index', compact('customers', 'cities'));
    }

    public function create()
    {
        $cities = City::where('status', 'active')->get();
        return view('customers.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'alt_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'city_id' => ['required', 'exists:cities,id'],
            'address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $count = Customer::withTrashed()->count() + 1001;
        $customerCode = 'SW-C-' . $count;

        $customer = Customer::create([
            'customer_code' => $customerCode,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'alt_phone' => $validated['alt_phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'city_id' => $validated['city_id'],
            'address' => $validated['address'],
            'notes' => $validated['notes'] ?? null,
        ]);

        CustomerAddress::create([
            'customer_id' => $customer->id,
            'city_id' => $validated['city_id'],
            'title' => 'Primary',
            'address_line' => $validated['address'],
            'is_default' => true,
        ]);

        AuditLogService::log(
            action: 'created',
            module: 'customers',
            recordId: $customer->id,
            description: "Created customer {$customer->name} ({$customer->phone})"
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'city',
            'addresses.city',
            'addresses.area',
            'serviceRequests' => fn($q) => $q->with(['serviceJob', 'city', 'currentWorker'])->orderBy('created_at', 'desc')
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $cities = City::where('status', 'active')->get();
        return view('customers.edit', compact('customer', 'cities'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'alt_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'city_id' => ['required', 'exists:cities,id'],
            'address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        AuditLogService::log(
            action: 'updated',
            module: 'customers',
            recordId: $customer->id,
            description: "Updated customer {$customer->name}"
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    /**
     * Fast Customer Search by Phone for Service Request Booking
     */
    public function searchByPhone(Request $request)
    {
        $phone = trim($request->query('phone'));
        if (empty($phone)) {
            return response()->json(['found' => false, 'customer' => null]);
        }

        $customer = Customer::with(['city', 'addresses'])->where('phone', $phone)->first();

        if ($customer) {
            $previousJobsCount = $customer->serviceRequests()->count();
            return response()->json([
                'found' => true,
                'customer' => [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'city_id' => $customer->city_id,
                    'city_name' => $customer->city?->name,
                    'address' => $customer->address,
                    'previous_jobs_count' => $previousJobsCount,
                ],
            ]);
        }

        return response()->json(['found' => false, 'customer' => null]);
    }
}
