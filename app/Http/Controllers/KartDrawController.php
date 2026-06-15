<?php

namespace App\Http\Controllers;

use App\Http\Requests\KartDrawUpdateRequest;
use App\Http\Requests\KartQueueRequest;
use App\Models\KartDraw;
use App\Models\KartDrawBatch;
use App\Models\StageCategory;
use App\Services\KartDrawService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KartDrawController extends Controller
{
    public function __construct(protected KartDrawService $kartDrawService)
    {
    }

    public function show(StageCategory $stageCategory): View
    {
        Gate::authorize('manage-championship');

        $stageCategory->load([
            'stage',
            'seasonCategory.category',
            'entries.pilot',
            'kartQueuePositions',
            'drawBatches.draws.stageCategoryEntry.pilot',
        ]);

        return view('stage-management.kart-draw', [
            'stageCategory' => $stageCategory,
            'latestBatch' => $this->kartDrawService->latestBatch($stageCategory),
            'queuePreview' => $this->kartDrawService->queuePreview($stageCategory),
        ]);
    }

    public function saveQueue(KartQueueRequest $request, StageCategory $stageCategory): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->kartDrawService->saveQueue($stageCategory, $request->validated('positions'));

        return back()->with('status', 'Fila de karts salva com sucesso.');
    }

    public function draw(StageCategory $stageCategory): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->kartDrawService->draw($stageCategory, request()->user());

        return back()->with('status', 'Sorteio realizado com sucesso.');
    }

    public function lock(KartDrawBatch $batch): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->kartDrawService->lock($batch);

        return back()->with('status', 'Sorteio travado com sucesso.');
    }

    public function unlock(KartDrawBatch $batch): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->kartDrawService->unlock($batch);

        return back()->with('status', 'Sorteio destravado com sucesso.');
    }

    public function update(KartDrawUpdateRequest $request, KartDraw $draw): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->kartDrawService->updatePosition($draw, $request->integer('queue_position'), $request->user());

        return back()->with('status', 'Posição da fila alterada manualmente com sucesso.');
    }

    public function exportCsv(KartDrawBatch $batch): StreamedResponse
    {
        Gate::authorize('manage-championship');

        $rows = $this->kartDrawService->exportRows($batch);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Piloto', 'Posição da fila', 'Kart', 'Ordem do sorteio', 'Manual', 'Atribuído em'], ';');

            foreach ($rows as $row) {
                fputcsv($output, array_values($row), ';');
            }

            fclose($output);
        }, 'sorteio-karts.csv');
    }
}
