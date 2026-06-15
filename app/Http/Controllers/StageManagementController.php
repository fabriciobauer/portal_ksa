<?php

namespace App\Http\Controllers;

use App\Http\Requests\KartChangeRequest;
use App\Http\Requests\OccurrenceRequest;
use App\Http\Requests\PointAdjustmentRequest;
use App\Http\Requests\QualifyingResultsRequest;
use App\Http\Requests\RaceResultsRequest;
use App\Http\Requests\StageCategoryEntryRequest;
use App\Http\Requests\StagePenaltyRequest;
use App\Http\Requests\WeighInRequest;
use App\Models\KartChange;
use App\Models\QualifyingResult;
use App\Models\Race;
use App\Models\RaceOccurrence;
use App\Models\RaceResult;
use App\Models\StageCategory;
use App\Models\StageCategoryEntry;
use App\Models\StagePenalty;
use App\Models\StageStanding;
use App\Models\WeighIn;
use App\Services\ChampionshipCalculatorService;
use App\Services\GridService;
use App\Services\PointAdjustmentService;
use App\Support\TimeFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StageManagementController extends Controller
{
    public function __construct(
        protected GridService $gridService,
        protected ChampionshipCalculatorService $calculator,
        protected PointAdjustmentService $pointAdjustmentService,
    ) {
    }

    public function show(StageCategory $stageCategory): View
    {
        Gate::authorize('manage-championship');

        $stageCategory->load([
            'stage.season',
            'seasonCategory.category',
            'entries.pilot',
            'entries.registration',
            'entries.qualifyingResult.kartChanges',
            'entries.kartDraws.batch',
            'entries.raceResults.race',
            'entries.penalties',
            'entries.occurrences',
            'entries.stageStanding.adjustments.user',
            'drawBatches.draws.stageCategoryEntry.pilot',
            'races.results.stageCategoryEntry.pilot',
        ]);

        $races = $stageCategory->races->keyBy('number');
        $raceOneGrid = $stageCategory->entries->whereNotNull('qualifyingResult')->isNotEmpty()
            ? $this->gridService->generateRaceOneGrid($stageCategory, false)
            : collect();
        $raceTwoGrid = ($races[1] ?? null) && $races[1]->results->isNotEmpty()
            ? $this->gridService->generateRaceTwoGrid($stageCategory, false)
            : collect();

        return view('stage-management.show', [
            'stageCategory' => $stageCategory,
            'latestKartBatch' => $stageCategory->drawBatches->sortByDesc('sequence')->first(),
            'races' => $races,
            'raceOneGrid' => $raceOneGrid,
            'raceTwoGrid' => $raceTwoGrid,
            'availablePilots' => $stageCategory->seasonCategory->registrations()->with('pilot')->get()->pluck('pilot'),
            'standings' => $stageCategory->entries
                ->pluck('stageStanding')
                ->filter()
                ->sortBy('stage_position'),
        ]);
    }

    public function storeEntry(StageCategory $stageCategory, StageCategoryEntryRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $pilotId = $request->integer('pilot_id');
        $confirmationStatus = $request->string('confirmation_status')->toString();
        $operationalLimit = $stageCategory->effectivePilotLimit();
        $currentEntry = $stageCategory->entries()
            ->where('pilot_id', $pilotId)
            ->first();

        if ($confirmationStatus === 'confirmed' && $currentEntry?->confirmation_status !== 'confirmed') {
            $confirmedCount = $stageCategory->entries()
                ->where('confirmation_status', 'confirmed')
                ->where('pilot_id', '!=', $pilotId)
                ->count();

            if ($confirmedCount >= $operationalLimit) {
                throw ValidationException::withMessages([
                    'confirmation_status' => "Esta etapa/categoria atingiu o limite operacional de {$operationalLimit} pilotos confirmados. Altere outro participante antes de confirmar mais um.",
                ]);
            }
        }

        StageCategoryEntry::query()->updateOrCreate(
            [
                'stage_category_id' => $stageCategory->id,
                'pilot_id' => $pilotId,
            ],
            [
                ...$request->safe()->except('pilot_id'),
                'created_by' => $request->user()->id,
            ],
        );

        $this->calculator->recalculateStageCategory($stageCategory);

        return back()->with('status', 'Participante da etapa atualizado com sucesso.');
    }

    public function saveQualifying(StageCategory $stageCategory, QualifyingResultsRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        DB::transaction(function () use ($request, $stageCategory): void {
            foreach ($request->validated('results') as $payload) {
                $stageCategory->entries()->findOrFail($payload['entry_id']);
                $kartNumber = ($payload['kart_number'] ?? null) !== '' ? ($payload['kart_number'] ?? null) : null;

                $result = QualifyingResult::query()->firstOrNew([
                    'stage_category_entry_id' => $payload['entry_id'],
                ]);

                $result->fill([
                    'initial_kart_number' => $result->initial_kart_number ?: $kartNumber,
                    'current_kart_number' => $kartNumber ?: $result->current_kart_number,
                    'lap_time_ms' => TimeFormatter::parseLapTime($payload['lap_time'] ?? null),
                    'status' => $payload['status'],
                    'notes' => $payload['notes'] ?? null,
                    'recorded_by' => $request->user()->id,
                    'confirmed_at' => now(),
                ])->save();
            }
        });

        $this->gridService->generateRaceOneGrid($stageCategory, true);
        $this->calculator->recalculateStageCategory($stageCategory);

        return back()->with('status', 'Tomada de tempo salva com sucesso.');
    }

    public function saveKartChange(StageCategory $stageCategory, KartChangeRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $entry = $stageCategory->entries()->findOrFail($request->integer('stage_category_entry_id'));
        $qualifyingResult = $entry->qualifyingResult;

        if (! $qualifyingResult) {
            throw ValidationException::withMessages([
                'stage_category_entry_id' => 'Cadastre a tomada de tempo antes de registrar uma troca de kart.',
            ]);
        }

        if (
            $request->string('reason_type')->toString() === 'regular'
            && $entry->qualifyingResult->kartChanges()->where('counts_as_regular_swap', true)->exists()
        ) {
            throw ValidationException::withMessages([
                'reason_type' => 'Cada piloto pode realizar apenas uma troca comum de kart na tomada.',
            ]);
        }

        $pastKarts = $entry->qualifyingResult->kartChanges->pluck('previous_kart_number')
            ->push($entry->qualifyingResult->initial_kart_number)
            ->filter()
            ->unique();

        if ($pastKarts->contains($request->integer('new_kart_number'))) {
            throw ValidationException::withMessages([
                'new_kart_number' => 'O piloto não pode retornar para um kart anteriormente utilizado.',
            ]);
        }

        $raceOne = Race::query()->where('stage_category_id', $stageCategory->id)->where('number', 1)->first();

        if ($raceOne && $raceOne->results()->exists() && $request->string('reason_type')->toString() !== 'breakdown') {
            throw ValidationException::withMessages([
                'reason_type' => 'Após o encerramento da tomada, somente trocas por quebra confirmada são permitidas.',
            ]);
        }

        KartChange::query()->create([
            'stage_category_entry_id' => $entry->id,
            'qualifying_result_id' => $qualifyingResult->id,
            'previous_kart_number' => $request->integer('previous_kart_number'),
            'new_kart_number' => $request->integer('new_kart_number'),
            'reason_type' => $request->string('reason_type')->toString(),
            'counts_as_regular_swap' => $request->string('reason_type')->toString() !== 'breakdown',
            'happened_at' => $request->date('happened_at'),
            'created_by' => $request->user()->id,
            'notes' => $request->input('notes'),
        ]);

        $qualifyingResult->forceFill([
            'current_kart_number' => $request->integer('new_kart_number'),
        ])->save();

        $this->gridService->generateRaceOneGrid($stageCategory, true);

        return back()->with('status', 'Troca de kart registrada com sucesso.');
    }

    public function saveRaceResults(StageCategory $stageCategory, int $raceNumber, RaceResultsRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $race = Race::query()
            ->where('stage_category_id', $stageCategory->id)
            ->where('number', $raceNumber)
            ->firstOrFail();

        DB::transaction(function () use ($request, $race, $stageCategory): void {
            foreach ($request->validated('results') as $payload) {
                $stageCategory->entries()->findOrFail($payload['entry_id']);
                $gridPosition = ($payload['grid_position'] ?? null) !== '' ? ($payload['grid_position'] ?? null) : null;
                $finishPosition = ($payload['finish_position'] ?? null) !== '' ? ($payload['finish_position'] ?? null) : null;
                $kartNumber = ($payload['kart_number'] ?? null) !== '' ? ($payload['kart_number'] ?? null) : null;

                $race->results()->updateOrCreate(
                    [
                        'stage_category_entry_id' => $payload['entry_id'],
                    ],
                    [
                        'grid_position' => $gridPosition,
                        'finish_position' => $finishPosition,
                        'kart_number' => $kartNumber,
                        'status' => $payload['status'],
                        'notes' => $payload['notes'] ?? null,
                    ],
                );
            }

            $race->forceFill([
                'is_result_confirmed' => true,
                'confirmed_at' => now(),
            ])->save();
        });

        if ($raceNumber === 1) {
            $this->gridService->generateRaceTwoGrid($stageCategory, true);
        }

        $this->calculator->recalculateStageCategory($stageCategory);

        return back()->with('status', "Resultado da Corrida {$raceNumber} salvo com sucesso.");
    }

    public function saveWeighIn(StageCategory $stageCategory, WeighInRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $entry = $stageCategory->entries()->findOrFail($request->integer('stage_category_entry_id'));
        $targetWeight = (float) ($stageCategory->seasonCategory->category->target_weight ?? 0);
        $tolerance = (float) app(\App\Services\SettingService::class)->get('stage.weigh_in_tolerance', 0.5);
        $combinedWeight = $request->input('combined_weight') !== null ? (float) $request->input('combined_weight') : null;

        WeighIn::query()->updateOrCreate(
            [
                'stage_category_entry_id' => $entry->id,
            ],
            [
                'kart_number' => $request->filled('kart_number') ? $request->integer('kart_number') : null,
                'combined_weight' => $combinedWeight,
                'tolerance_used' => $tolerance,
                'within_tolerance' => $combinedWeight !== null ? $combinedWeight >= ($targetWeight - $tolerance) : null,
                'exception_no_spare_kart' => $request->boolean('exception_no_spare_kart'),
                'notes' => $request->input('notes'),
                'recorded_by' => $request->user()->id,
                'weighed_at' => now(),
            ],
        );

        $this->calculator->recalculateStageCategory($stageCategory);

        return back()->with('status', 'Pesagem salva com sucesso.');
    }

    public function addPenalty(StageCategory $stageCategory, StagePenaltyRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $entry = $stageCategory->entries()->findOrFail($request->integer('stage_category_entry_id'));
        $type = $request->string('type')->toString();
        $isPodiumPenalty = in_array($type, ['podium_attire', 'podium_absent'], true);
        $isDisqualification = in_array($type, ['stage_dsq', 'race_dsq'], true) || $request->boolean('is_disqualification');

        StagePenalty::query()->create([
            'stage_category_entry_id' => $entry->id,
            'race_id' => $request->integer('race_id') ?: null,
            'type' => $type,
            'penalty_scope' => $request->string('penalty_scope')->toString(),
            'points_delta' => $request->filled('points_delta') ? (float) $request->input('points_delta') : ($isPodiumPenalty ? -1 : 0),
            'capped_group' => $request->input('capped_group', $isPodiumPenalty ? 'podium_conduct' : null),
            'is_disqualification' => $isDisqualification,
            'affects_discard_block' => $isDisqualification || $request->boolean('affects_discard_block'),
            'reason' => $request->input('reason'),
            'description' => $request->input('description'),
            'created_by' => $request->user()->id,
            'applied_at' => now(),
        ]);

        return back()->with('status', 'Penalidade registrada com sucesso.');
    }

    public function addOccurrence(StageCategory $stageCategory, OccurrenceRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $entry = $stageCategory->entries()->findOrFail($request->integer('stage_category_entry_id'));
        $type = $request->string('type')->toString();
        $secondsPenalty = match ($type) {
            'time_penalty_5' => 5,
            'time_penalty_10' => 10,
            default => null,
        };

        $evidencePath = $request->hasFile('evidence')
            ? $request->file('evidence')->store('occurrences', 'public')
            : null;

        $warningCount = $entry->occurrences()->where('type', 'warning')->count();
        $autoDisqualified = $type === 'warning' && $warningCount + 1 >= 3;

        RaceOccurrence::query()->create([
            'stage_category_entry_id' => $entry->id,
            'race_id' => $request->integer('race_id') ?: null,
            'type' => $type,
            'seconds_penalty' => $secondsPenalty,
            'auto_disqualified' => $autoDisqualified,
            'description' => $request->input('description'),
            'evidence_path' => $evidencePath,
            'created_by' => $request->user()->id,
            'issued_at' => now(),
        ]);

        return back()->with('status', 'Ocorrência registrada com sucesso.');
    }

    public function adjustPoints(PointAdjustmentRequest $request, StageStanding $standing): RedirectResponse
    {
        Gate::authorize('manage-points-override');

        $type = $request->string('type')->toString();

        match ($type) {
            'adjustment' => $this->pointAdjustmentService->applyAdjustment(
                $standing,
                (float) $request->input('value', 0),
                $request->string('reason')->toString(),
                $request->user(),
            ),
            'override' => $this->pointAdjustmentService->applyOverride(
                $standing,
                (float) $request->input('value', 0),
                $request->string('reason')->toString(),
                $request->user(),
            ),
            'revert_override' => $this->pointAdjustmentService->revertOverride(
                $standing,
                $request->string('reason')->toString(),
                $request->user(),
            ),
        };

        return back()->with('status', 'Pontuação ajustada com sucesso.');
    }

    public function resolveTie(Request $request, StageStanding $standing): RedirectResponse
    {
        Gate::authorize('manage-points-override');

        $validated = $request->validate([
            'stage_position' => ['required', 'integer', 'min:1'],
            'tie_break_notes' => ['required', 'string'],
        ]);

        $standing->forceFill([
            'stage_position' => $validated['stage_position'],
            'tie_break_notes' => $validated['tie_break_notes'],
            'tie_breaker_resolved_manually' => true,
            'is_technical_tie' => false,
        ])->save();

        return back()->with('status', 'Desempate manual registrado com sucesso.');
    }

    public function recalculate(StageCategory $stageCategory): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->calculator->recalculateStageCategory($stageCategory);

        return back()->with('status', 'Classificações recalculadas com sucesso.');
    }
}
