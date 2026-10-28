<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('courses')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $parentCategories = Category::orderBy('name')->get();

        return view('admin.categories.create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $slug = Str::slug($validated['name']) . '-' . Str::lower(Str::random(4));

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parentCategories = Category::where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category updated successfully.');
    }

    /**
     * Delete category. Blocked if category still has courses.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->withErrors([
                'error' => "Cannot delete category [{$category->name}] because it still has associated courses.",
            ]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category deleted successfully.');
    }
}
