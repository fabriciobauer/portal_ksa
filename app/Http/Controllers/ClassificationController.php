<?php

namespace App\Http\Controllers;

use App\Models\ChampionshipPoint;
use App\Models\SeasonCategory;
use App\Models\StageCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ClassificationController extends Controller
{
    public function stage(StageCategory $stageCategory): View
    {
        Gate::authorize('manage-championship');

        $stageCategory->load([
            'stage.season',
            'seasonCategory.category',
            'entries.pilot',
            'entries.stageStanding.adjustments.user',
        ]);

        $standings = $stageCategory->entries
            ->pluck('stageStanding')
            ->filter()
            ->sortBy('stage_position');

        return view('classifications.stage', compact('stageCategory', 'standings'));
    }

    public function championship(SeasonCategory $seasonCategory): View
    {
        Gate::authorize('manage-championship');

        $seasonCategory->load(['season', 'category', 'championshipStandings.pilot']);
        $points = $seasonCategory->championshipStandings()->with('pilot')->orderBy('final_position')->get();
        $stagePoints = $seasonCategory->stageCategories()->with('stage')->get()->pluck('stage')->sortBy('stage_number');
        $pointMatrix = ChampionshipPoint::query()
            ->where('season_category_id', $seasonCategory->id)
            ->with('stage')
            ->get()
            ->groupBy('pilot_id');

        return view('classifications.championship', compact('seasonCategory', 'points', 'stagePoints', 'pointMatrix'));
    }

    public function stageCsv(StageCategory $stageCategory): StreamedResponse
    {
        Gate::authorize('manage-championship');

        $rows = $this->stageRows($stageCategory);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Posição', 'Piloto', 'Bruto Etapa', 'Pontos Campeonato', 'Desclassificado'], ';');
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }
            fclose($output);
        }, 'classificacao-etapa.csv');
    }

    public function championshipCsv(SeasonCategory $seasonCategory): StreamedResponse
    {
        Gate::authorize('manage-championship');

        $rows = $this->championshipRows($seasonCategory);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Posição', 'Piloto', 'Pontos Válidos', 'Pontos Descartados', 'Elegível à promoção'], ';');
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }
            fclose($output);
        }, 'classificacao-campeonato.csv');
    }

    public function stagePdf(StageCategory $stageCategory)
    {
        Gate::authorize('manage-championship');

        $stageCategory->load(['stage.season', 'seasonCategory.category', 'entries.pilot', 'entries.stageStanding']);
        $standings = $stageCategory->entries->pluck('stageStanding')->filter()->sortBy('stage_position');

        return Pdf::loadView('classifications.exports.stage-pdf', compact('stageCategory', 'standings'))
            ->download('classificacao-etapa.pdf');
    }

    public function championshipPdf(SeasonCategory $seasonCategory)
    {
        Gate::authorize('manage-championship');

        $seasonCategory->load(['season', 'category', 'championshipStandings.pilot']);
        $points = $seasonCategory->championshipStandings()->with('pilot')->orderBy('final_position')->get();

        return Pdf::loadView('classifications.exports.championship-pdf', compact('seasonCategory', 'points'))
            ->download('classificacao-campeonato.pdf');
    }

    protected function stageRows(StageCategory $stageCategory): Collection
    {
        $stageCategory->load(['entries.pilot', 'entries.stageStanding']);

        return $stageCategory->entries
            ->pluck('stageStanding')
            ->filter()
            ->sortBy('stage_position')
            ->map(fn ($standing) => [
                $standing->stage_position,
                $standing->stageCategoryEntry->pilot->displayName(),
                $standing->gross_stage_points,
                $standing->championship_points,
                $standing->is_disqualified ? 'Sim' : 'Não',
            ]);
    }

    protected function championshipRows(SeasonCategory $seasonCategory): Collection
    {
        return $seasonCategory->championshipStandings()
            ->with('pilot')
            ->orderBy('final_position')
            ->get()
            ->map(fn ($standing) => [
                $standing->final_position,
                $standing->pilot->displayName(),
                $standing->total_valid_points,
                $standing->discarded_points,
                $standing->promotion_eligible ? 'Sim' : 'Não',
            ]);
    }
}
