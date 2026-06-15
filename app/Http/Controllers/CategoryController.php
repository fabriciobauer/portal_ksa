<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-championship');

        $categories = Category::query()->withCount('seasonCategories')->orderBy('name')->paginate(12);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        Gate::authorize('admin-only');

        return view('categories.form', ['category' => new Category()]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Gate::authorize('admin-only');

        $category = Category::query()->create($request->validated());

        return redirect()->route('categories.edit', $category)->with('status', 'Categoria criada com sucesso.');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('manage-championship');

        return view('categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $category->update($request->validated());

        return back()->with('status', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('admin-only');

        if ($category->seasonCategories()->exists()) {
            $category->update(['is_active' => false]);

            return back()->with('status', 'Categoria vinculada a temporadas. Ela foi apenas inativada.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoria removida com sucesso.');
    }
}
