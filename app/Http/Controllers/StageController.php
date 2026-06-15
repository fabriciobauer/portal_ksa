<?php

namespace App\Http\Controllers;

use App\Http\Requests\StageRequest;
use App\Models\Season;
use App\Models\Stage;
use App\Services\SettingService;
use App\Services\StageProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StageController extends Controller
{
    public function __construct(
        protected StageProvisioningService $stageProvisioningService,
        protected SettingService $settingService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Stage::class);

        $stages = Stage::query()
            ->with(['season', 'stageCategories.seasonCategory.category'])
            ->orderByDesc('stage_date')
            ->paginate(15);

        return view('stages.index', compact('stages'));
    }

    public function create(): View
    {
        $this->authorize('create', Stage::class);

        return view('stages.form', [
            'stage' => new Stage([
                'briefing_time' => $this->settingService->get('stage.default_briefing_time', '08:00'),
            ]),
            'seasons' => Season::query()->orderByDesc('start_date')->get(),
        ]);
    }

    public function store(StageRequest $request): RedirectResponse
    {
        $this->authorize('create', Stage::class);

        $stage = Stage::query()->create($request->validated());
        $this->stageProvisioningService->provisionStage($stage->fresh('season'));

        return redirect()->route('stages.edit', $stage)->with('status', 'Etapa criada com sucesso.');
    }

    public function show(Stage $stage): View
    {
        $this->authorize('view', $stage);

        $stage->load(['season', 'stageCategories.seasonCategory.category', 'stageCategories.entries']);

        return view('stages.show', compact('stage'));
    }

    public function edit(Stage $stage): View
    {
        $this->authorize('update', $stage);

        return view('stages.form', [
            'stage' => $stage,
            'seasons' => Season::query()->orderByDesc('start_date')->get(),
        ]);
    }

    public function update(StageRequest $request, Stage $stage): RedirectResponse
    {
        $this->authorize('update', $stage);

        $stage->update($request->validated());
        $this->stageProvisioningService->provisionStage($stage->fresh('season'));

        return back()->with('status', 'Etapa atualizada com sucesso.');
    }

    public function destroy(Stage $stage): RedirectResponse
    {
        Gate::authorize('admin-only');

        $hasResults = $stage->stageCategories()->whereHas('entries.raceResults')->exists();

        if ($hasResults) {
            return back()->withErrors('Não é possível remover uma etapa que já possui resultados lançados.');
        }

        $stage->delete();

        return redirect()->route('stages.index')->with('status', 'Etapa removida com sucesso.');
    }
}
