<?php

use App\Models\Season;
use App\Models\Stage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('championship:recalculate {--season=} {--stage=}', function () {
    $calculator = app(\App\Services\ChampionshipCalculatorService::class);
    $seasonId = $this->option('season');
    $stageId = $this->option('stage');

    if ($stageId) {
        $stage = Stage::query()->findOrFail($stageId);
        $calculator->recalculateStage($stage);
        $this->info("Etapa {$stage->name} recalculada com sucesso.");

        return;
    }

    if ($seasonId) {
        $season = Season::query()->findOrFail($seasonId);
        $calculator->recalculateSeason($season);
        $this->info("Temporada {$season->name} recalculada com sucesso.");

        return;
    }

    Season::query()->get()->each(fn (Season $season) => $calculator->recalculateSeason($season));
    $this->info('Todas as temporadas foram recalculadas com sucesso.');
})->purpose('Recalcula pontuações e classificações do campeonato');
