<?php

namespace App\Http\Controllers;

use App\Models\ChampionshipPoint;
use App\Models\Season;
use App\Models\SeasonCategory;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(): View
    {
        $season = Season::query()
            ->with([
                'stages' => fn ($query) => $query->orderBy('stage_number'),
                'seasonCategories' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with([
                        'category',
                        'stageCategories.stage',
                        'championshipStandings' => fn ($standingsQuery) => $standingsQuery
                            ->with('pilot')
                            ->orderByRaw('COALESCE(final_position, 9999)')
                            ->orderByDesc('total_valid_points')
                            ->orderBy('pilot_id'),
                    ]),
            ])
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        $seasonCategories = $season?->seasonCategories ?? collect();
        $pointMatrix = ChampionshipPoint::query()
            ->whereIn('season_category_id', $seasonCategories->pluck('id'))
            ->with('stage')
            ->get()
            ->groupBy('season_category_id');

        $categories = $seasonCategories
            ->sortBy(fn (SeasonCategory $seasonCategory) => $seasonCategory->category->name)
            ->values()
            ->map(fn (SeasonCategory $seasonCategory) => $this->buildCategoryData(
                $seasonCategory,
                $pointMatrix->get($seasonCategory->id, collect()),
            ));

        $latestPublishedStage = $season?->stages
            ->sortByDesc('stage_number')
            ->firstWhere('status', 'finished') ?? $season?->stages->sortByDesc('stage_number')->first();

        $rankedPilotCount = $categories->sum(fn (array $category) => $category['pilot_count']);
        $siteHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (! $siteHost || $siteHost === 'localhost') {
            $siteHost = 'ksaracing.com.br';
        }

        return view('public.home', compact(
            'season',
            'categories',
            'latestPublishedStage',
            'rankedPilotCount',
            'siteHost',
        ));
    }

    protected function buildCategoryData(SeasonCategory $seasonCategory, Collection $points): array
    {
        $stages = $seasonCategory->stageCategories
            ->pluck('stage')
            ->filter()
            ->unique('id')
            ->sortBy('stage_number')
            ->values();

        $standings = $seasonCategory->championshipStandings->values();
        $pointsByPilot = $points->groupBy('pilot_id');

        $stageRows = $standings->map(function ($standing) use ($stages, $pointsByPilot) {
            $pilotPoints = $pointsByPilot->get($standing->pilot_id, collect())->keyBy('stage_id');

            return [
                'standing' => $standing,
                'pilot' => $standing->pilot,
                'stages' => $stages->map(function ($stage) use ($pilotPoints) {
                    $point = $pilotPoints->get($stage->id);

                    return [
                        'stage' => $stage,
                        'valid_points' => $point?->valid_points,
                        'is_discarded' => (bool) ($point?->is_discarded ?? false),
                        'discard_blocked' => (bool) ($point?->discard_blocked ?? false),
                    ];
                }),
            ];
        });

        return [
            'id' => $seasonCategory->id,
            'name' => $seasonCategory->category->name,
            'description' => $this->sanitizePublicDescription($seasonCategory->category->description),
            'standings' => $standings,
            'stage_rows' => $stageRows,
            'stages' => $stages,
            'pilot_count' => $standings->count(),
            'stage_count' => $stages->count(),
        ];
    }

    protected function sanitizePublicDescription(?string $description): ?string
    {
        if (blank($description)) {
            return null;
        }

        $sanitized = trim((string) $description);

        if (str_contains(mb_strtolower($sanitized), 'seeder')) {
            return null;
        }

        return $sanitized;
    }
}
