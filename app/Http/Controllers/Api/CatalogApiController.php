<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\ServiceJob;
use Illuminate\Http\Request;

class CatalogApiController extends Controller
{
    /**
     * Public API endpoint for Serwizo Website to fetch live categories and jobs
     * Supports optional city-based filtering: ?city=lahore or ?city_id=1
     */
    public function getCatalog(Request $request)
    {
        $cityId = null;
        if ($request->has('city_id')) {
            $cityId = (int)$request->query('city_id');
        } elseif ($cityName = $request->query('city')) {
            $city = City::whereRaw('LOWER(name) = ?', [strtolower(trim($cityName))])->first();
            $cityId = $city?->id;
        }

        $categoriesQuery = Category::where('status', 'active')->orderBy('title');

        if ($cityId) {
            $categoriesQuery->forCity($cityId);
        }

        $categories = $categoriesQuery->with([
            'subCategories' => function ($q) {
                $q->where('status', 'active')->orderBy('title');
            },
            'serviceJobs' => function ($q) {
                $q->where('status', 'active')->orderBy('title');
            },
            'cities' => function ($q) {
                $q->where('cities.status', 'active');
            }
        ])->get();

        $data = $categories->map(function ($cat) use ($cityId) {
            $jobs = $cat->serviceJobs->map(function ($job) use ($cityId) {
                $cityPrice = $job->getPriceForCity($cityId);
                $regularPrice = (float)($cityPrice['customer_price'] ?? $job->default_customer_price);
                $isPromo = (bool)$job->is_promotional;
                $promoPrice = $isPromo && $job->promotional_customer_price !== null ? (float)$job->promotional_customer_price : null;
                $effectivePrice = ($isPromo && $promoPrice !== null) ? $promoPrice : $regularPrice;

                $discountPercent = null;
                if ($isPromo && $promoPrice !== null && $regularPrice > $promoPrice && $regularPrice > 0) {
                    $discountPercent = (int)round((($regularPrice - $promoPrice) / $regularPrice) * 100);
                }

                return [
                    'id' => (string)$job->id,
                    'database_id' => $job->id,
                    'name' => $job->title,
                    'title' => $job->title,
                    'code' => $job->code,
                    'description' => $job->description ?: 'Inspection, diagnostics, genuine workmanship and service execution.',
                    'turnaround' => $job->estimated_duration_minutes ? "{$job->estimated_duration_minutes} Mins" : '45-90 Mins',
                    'duration_minutes' => $job->estimated_duration_minutes,
                    'price' => $effectivePrice,
                    'regular_price' => $regularPrice,
                    'promotional_price' => $promoPrice,
                    'is_promotional' => $isPromo,
                    'discount_percentage' => $discountPercent,
                    'sub_category_id' => $job->sub_category_id,
                    'sub_category_title' => $job->subCategory?->title,
                    'is_brand_job' => (bool)$job->is_brand_job,
                    'is_free_installation' => (bool)$job->is_free_installation,
                    'brand_name' => $job->brand?->name,
                ];
            });

            return [
                'id' => $cat->slug ?: (string)$cat->id,
                'database_id' => $cat->id,
                'name' => $cat->title,
                'title' => $cat->title,
                'slug' => $cat->slug,
                'description' => $cat->description ?: 'Verified professional doorstep services with standard warranty.',
                'icon' => $cat->icon ?: 'fa-wrench',
                'active_cities' => $cat->cities->count() === 0 ? ['All Cities'] : $cat->cities->pluck('name')->all(),
                'sub_categories' => $cat->subCategories->map(fn($sc) => [
                    'id' => $sc->id,
                    'title' => $sc->title,
                    'slug' => $sc->slug,
                    'description' => $sc->description,
                ]),
                'jobs' => $jobs,
                'subJobs' => $jobs, // compatibility with frontend components
            ];
        });

        return response()->json([
            'success' => true,
            'city_id' => $cityId,
            'total_categories' => $data->count(),
            'data' => $data,
        ], 200, [
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * List active cities for website location selector
     */
    public function getCities()
    {
        $cities = City::where('status', 'active')->orderBy('name')->get(['id', 'name', 'province']);

        return response()->json([
            'success' => true,
            'data' => $cities,
        ], 200, [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
