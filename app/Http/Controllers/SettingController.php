<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsUpdateRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService)
    {
    }

    public function edit(): View
    {
        Gate::authorize('manage-settings');

        return view('settings.edit', [
            'settings' => $this->settingService->allGrouped(),
            'values' => $this->settingService->all()->pluck('value', 'key'),
        ]);
    }

    public function update(SettingsUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('manage-settings');

        $settings = $request->validated('settings');

        $this->settingService->saveMany([
            'championship.name' => data_get($settings, 'championship.name'),
            'championship.organization' => data_get($settings, 'championship.organization'),
            'karts.range_start' => data_get($settings, 'karts.range_start'),
            'karts.range_end' => data_get($settings, 'karts.range_end'),
            'scoring.points_table' => data_get($settings, 'scoring.points_table'),
            'scoring.discard_count' => data_get($settings, 'scoring.discard_count'),
            'scoring.bonus_no_spare_kart_behavior' => data_get($settings, 'scoring.bonus_no_spare_kart_behavior'),
            'stage.default_briefing_time' => data_get($settings, 'stage.default_briefing_time'),
            'stage.weigh_in_tolerance' => data_get($settings, 'stage.weigh_in_tolerance'),
            'stage.allow_single_entries' => data_get($settings, 'stage.allow_single_entries', false),
            'stage.briefing_adjustment_mode' => data_get($settings, 'stage.briefing_adjustment_mode'),
            'standings.championship_tiebreak_scope' => data_get($settings, 'standings.championship_tiebreak_scope'),
            'standings.ambiguity_behavior' => data_get($settings, 'standings.ambiguity_behavior'),
        ]);

        return back()->with('status', 'Configurações atualizadas com sucesso.');
    }
}
