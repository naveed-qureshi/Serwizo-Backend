<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = SubCategory::with(['category'])->withCount('serviceJobs');

        if ($catId = $request->query('category_id')) {
            $query->where('category_id', $catId);
        }

        $subCategories = $query->orderBy('title')->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->get();

        return view('sub_categories.index', compact('subCategories', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        return view('sub_categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $subCategory = SubCategory::create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        AuditLogService::log(
            action: 'created',
            module: 'categories',
            recordId: $subCategory->id,
            description: "Created subcategory: {$subCategory->title}"
        );

        return redirect()->route('sub-categories.index')->with('success', 'Subcategory created successfully.');
    }

    public function edit(SubCategory $subCategory)
    {
        $categories = Category::where('status', 'active')->get();
        return view('sub_categories.edit', compact('subCategory', 'categories'));
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $subCategory->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        AuditLogService::log(
            action: 'updated',
            module: 'categories',
            recordId: $subCategory->id,
            description: "Updated subcategory: {$subCategory->title}"
        );

        return redirect()->route('sub-categories.index')->with('success', 'Subcategory updated successfully.');
    }

    public function getByCategory(Category $category)
    {
        return response()->json([
            'success' => true,
            'data' => $category->subCategories()->where('status', 'active')->get(['id', 'title']),
        ]);
    }

    public function destroy(SubCategory $subCategory)
    {
        $jobCount = $subCategory->serviceJobs()->count();
        if ($jobCount > 0) {
            return back()->with('error', "Cannot delete subcategory '{$subCategory->title}' because it has {$jobCount} active job services assigned to it. Please reassign or delete the jobs first.");
        }

        $title = $subCategory->title;
        $id = $subCategory->id;
        $subCategory->delete();

        AuditLogService::log(
            action: 'deleted',
            module: 'categories',
            recordId: $id,
            description: "Deleted subcategory: {$title}"
        );

        return redirect()->route('sub-categories.index')->with('success', "Subcategory '{$title}' deleted successfully.");
    }
}
