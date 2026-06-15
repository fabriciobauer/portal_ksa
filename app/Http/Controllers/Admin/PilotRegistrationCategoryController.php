<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PilotRegistrationCategoryRequest;
use App\Models\PilotRegistrationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PilotRegistrationCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-championship');

        return view('admin.pilot-registration-categories.index', [
            'categories' => PilotRegistrationCategory::query()
                ->withCount('registrations')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage-championship');

        return view('admin.pilot-registration-categories.form', [
            'category' => new PilotRegistrationCategory(),
        ]);
    }

    public function store(PilotRegistrationCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        PilotRegistrationCategory::query()->create([
            ...$request->validated(),
            'slug' => $this->uniqueSlug($request->string('name')->toString()),
        ]);

        return redirect()
            ->route('admin.registration-categories.index')
            ->with('status', 'Categoria de inscrição criada com sucesso.');
    }

    public function edit(PilotRegistrationCategory $pilotRegistrationCategory): View
    {
        Gate::authorize('manage-championship');

        return view('admin.pilot-registration-categories.form', [
            'category' => $pilotRegistrationCategory,
        ]);
    }

    public function update(
        PilotRegistrationCategoryRequest $request,
        PilotRegistrationCategory $pilotRegistrationCategory,
    ): RedirectResponse {
        Gate::authorize('manage-championship');

        $pilotRegistrationCategory->update([
            ...$request->validated(),
            'slug' => $this->uniqueSlug(
                $request->string('name')->toString(),
                $pilotRegistrationCategory,
            ),
        ]);

        return back()->with('status', 'Categoria de inscrição atualizada com sucesso.');
    }

    public function updateStatus(
        Request $request,
        PilotRegistrationCategory $pilotRegistrationCategory,
    ): RedirectResponse {
        Gate::authorize('manage-championship');

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $pilotRegistrationCategory->update([
            'is_active' => (bool) $data['is_active'],
        ]);

        return back()->with(
            'status',
            $pilotRegistrationCategory->is_active
                ? 'Categoria ativada com sucesso.'
                : 'Categoria desativada com sucesso.',
        );
    }

    protected function uniqueSlug(string $name, ?PilotRegistrationCategory $category = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'categoria-inscricao';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            PilotRegistrationCategory::query()
                ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
