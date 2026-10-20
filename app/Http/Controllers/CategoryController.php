<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['cities'])
            ->withCount(['subCategories', 'serviceJobs'])
            ->orderBy('title')
            ->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $cities = City::where('status', 'active')->orderBy('name')->get();
        return view('categories.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150', 'unique:categories,title'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'coverage_type' => ['nullable', Rule::in(['all', 'selected'])],
            'city_ids' => ['nullable', 'array'],
            'city_ids.*' => ['exists:cities,id'],
        ]);

        $category = Category::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'fa-th-large',
            'status' => $validated['status'],
        ]);

        // Sync City coverage
        if (($validated['coverage_type'] ?? 'all') === 'selected' && !empty($validated['city_ids'])) {
            $syncData = [];
            foreach ($validated['city_ids'] as $cId) {
                $syncData[$cId] = ['is_active' => true];
            }
            $category->cities()->sync($syncData);
        } else {
            $category->cities()->detach(); // empty means active in all cities
        }

        AuditLogService::log(
            action: 'created',
            module: 'categories',
            recordId: $category->id,
            description: "Created category: {$category->title}"
        );

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $category->load('cities');
        $cities = City::where('status', 'active')->orderBy('name')->get();
        return view('categories.edit', compact('category', 'cities'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150', Rule::unique('categories', 'title')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'coverage_type' => ['nullable', Rule::in(['all', 'selected'])],
            'city_ids' => ['nullable', 'array'],
            'city_ids.*' => ['exists:cities,id'],
        ]);

        $category->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? $category->icon,
            'status' => $validated['status'],
        ]);

        // Sync City coverage
        if (($validated['coverage_type'] ?? 'all') === 'selected' && !empty($validated['city_ids'])) {
            $syncData = [];
            foreach ($validated['city_ids'] as $cId) {
                $syncData[$cId] = ['is_active' => true];
            }
            $category->cities()->sync($syncData);
        } else {
            $category->cities()->detach(); // empty means active in all cities
        }

        AuditLogService::log(
            action: 'updated',
            module: 'categories',
            recordId: $category->id,
            description: "Updated category: {$category->title}"
        );

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $jobCount = $category->serviceJobs()->count();
        $subCount = $category->subCategories()->count();

        if ($jobCount > 0 || $subCount > 0) {
            return back()->with('error', "Cannot delete category '{$category->title}' because it has {$subCount} subcategories and {$jobCount} job services assigned to it. Please remove or reassign them first.");
        }

        $title = $category->title;
        $id = $category->id;
        $category->cities()->detach();
        $category->delete();

        AuditLogService::log(
            action: 'deleted',
            module: 'categories',
            recordId: $id,
            description: "Deleted category: {$title}"
        );

        return redirect()->route('categories.index')->with('success', "Category '{$title}' deleted successfully.");
    }
}
