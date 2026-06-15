<?php

namespace App\Http\Controllers;

use App\Models\ChampionshipStanding;
use App\Models\Season;
use App\Models\SeasonCategory;
use App\Models\Stage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $currentSeason = Season::query()
            ->current()
            ->with(['seasonCategories.category', 'stages'])
            ->first();

        $nextStages = Stage::query()
            ->with('season')
            ->whereDate('stage_date', '>=', now()->toDateString())
            ->orderBy('stage_date')
            ->take(5)
            ->get();

        $latestStage = Stage::query()
            ->with('season')
            ->where('status', 'closed')
            ->latest('stage_date')
            ->first();

        $leaders = ChampionshipStanding::query()
            ->with(['seasonCategory.category', 'pilot'])
            ->where('final_position', 1)
            ->whereHas('seasonCategory.season', fn ($query) => $query->where('is_current', true))
            ->get();

        $promotionAlerts = ChampionshipStanding::query()
            ->with(['seasonCategory.category', 'pilot'])
            ->where('promotion_eligible', true)
            ->whereHas('seasonCategory.season', fn ($query) => $query->where('is_current', true))
            ->orderBy('final_position')
            ->get();

        $categoryOccupancy = SeasonCategory::query()
            ->with(['category', 'season', 'registrations'])
            ->when($currentSeason, fn ($query) => $query->where('season_id', $currentSeason->id))
            ->get()
            ->map(function (SeasonCategory $seasonCategory) {
                return [
                    'season_category' => $seasonCategory,
                    'confirmed' => $seasonCategory->registrations->where('status', 'confirmed')->count(),
                    'waiting' => $seasonCategory->registrations->where('status', 'waiting')->count(),
                    'operational_limit' => $seasonCategory->effectivePilotLimit(),
                ];
            });

        return view('dashboard.index', compact(
            'currentSeason',
            'nextStages',
            'latestStage',
            'leaders',
            'promotionAlerts',
            'categoryOccupancy',
        ));
    }
}
