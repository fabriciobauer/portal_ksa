<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeasonRequest;
use App\Models\Category;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Services\StageProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function __construct(protected StageProvisioningService $stageProvisioningService)
    {
    }

    public function index(): View
    {
        Gate::authorize('manage-championship');

        $seasons = Season::query()
            ->with(['seasonCategories.category'])
            ->withCount('stages')
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->paginate(12);

        return view('seasons.index', compact('seasons'));
    }

    public function create(): View
    {
        Gate::authorize('admin-only');

        $categories = Category::query()->orderBy('name')->get();

        return view('seasons.form', [
            'season' => new Season(),
            'categories' => $categories,
            'selectedCategories' => collect(),
        ]);
    }

    public function store(SeasonRequest $request): RedirectResponse
    {
        Gate::authorize('admin-only');

        $season = DB::transaction(function () use ($request) {
            if ($request->boolean('is_current')) {
                Season::query()->update(['is_current' => false]);
            }

            $season = Season::query()->create($request->safe()->except(['category_ids', 'pilot_limit_overrides']));
            $this->syncSeasonCategories($season, $request->input('category_ids', []), $request->input('pilot_limit_overrides', []));

            return $season;
        });

        return redirect()->route('seasons.edit', $season)->with('status', 'Temporada criada com sucesso.');
    }

    public function show(Season $season): View
    {
        Gate::authorize('manage-championship');

        $season->load(['seasonCategories.category', 'seasonCategories.registrations', 'stages']);

        return view('seasons.show', compact('season'));
    }

    public function edit(Season $season): View
    {
        Gate::authorize('manage-championship');

        $categories = Category::query()->orderBy('name')->get();
        $selectedCategories = $season->seasonCategories()->pluck('category_id');

        return view('seasons.form', compact('season', 'categories', 'selectedCategories'));
    }

    public function update(SeasonRequest $request, Season $season): RedirectResponse
    {
        Gate::authorize('manage-championship');

        DB::transaction(function () use ($request, $season): void {
            if ($request->boolean('is_current')) {
                Season::query()->whereKeyNot($season->id)->update(['is_current' => false]);
            }

            $season->update($request->safe()->except(['category_ids', 'pilot_limit_overrides']));
            $this->syncSeasonCategories($season, $request->input('category_ids', []), $request->input('pilot_limit_overrides', []));

            foreach ($season->stages as $stage) {
                $this->stageProvisioningService->provisionStage($stage);
            }
        });

        return back()->with('status', 'Temporada atualizada com sucesso.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        Gate::authorize('admin-only');

        if ($season->stages()->exists()) {
            return back()->withErrors('Não é possível remover uma temporada que já possui etapas.');
        }

        $season->delete();

        return redirect()->route('seasons.index')->with('status', 'Temporada removida com sucesso.');
    }

    protected function syncSeasonCategories(Season $season, array $categoryIds, array $pilotLimitOverrides): void
    {
        $existing = $season->seasonCategories()->get()->keyBy('category_id');

        foreach ($categoryIds as $categoryId) {
            SeasonCategory::query()->updateOrCreate(
                [
                    'season_id' => $season->id,
                    'category_id' => $categoryId,
                ],
                [
                    'pilot_limit' => $pilotLimitOverrides[$categoryId] ?? null,
                    'is_active' => true,
                ],
            );
        }

        $existing
            ->keys()
            ->diff($categoryIds)
            ->each(fn ($categoryId) => $existing[$categoryId]->update(['is_active' => false]));
    }
}
