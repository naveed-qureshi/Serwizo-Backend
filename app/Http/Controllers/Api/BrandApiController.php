<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use Illuminate\Http\Request;

class BrandApiController extends Controller
{
    /**
     * Get active brands list for mobile app.
     */
    public function getBrands(Request $request)
    {
        $brands = Brand::where('status', 'active')
            ->select('id', 'name', 'code', 'default_installation_rate', 'phone', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Customer quick phone lookup.
     */
    public function lookupCustomer(Request $request)
    {
        $phone = $request->query('phone');
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Phone parameter required.'], 400);
        }

        // Clean phone
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $customer = Customer::with('city')
            ->where('phone', 'like', "%{$phone}%")
            ->orWhere('phone', 'like', "%{$cleanPhone}%")
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => true,
                'found' => false,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'alt_phone' => $customer->alt_phone,
                'address' => $customer->address,
                'city_id' => $customer->city_id,
                'city_name' => $customer->city?->name,
            ],
        ]);
    }

    /**
     * Get reference data (Cities, Categories with jobs).
     */
    public function getReferenceData(Request $request)
    {
        $cities = City::where('status', 'active')->select('id', 'name', 'state')->orderBy('name')->get();
        $categories = Category::where('status', 'active')
            ->with(['subCategories' => fn($q) => $q->where('status', 'active'), 'serviceJobs' => fn($q) => $q->where('status', 'active')])
            ->orderBy('title')
            ->get();
        $brands = Brand::where('status', 'active')->select('id', 'name', 'code', 'default_installation_rate')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'cities' => $cities,
                'categories' => $categories,
                'brands' => $brands,
            ],
        ]);
    }
}
