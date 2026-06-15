<?php

use App\Http\Controllers\Admin\PilotRegistrationCategoryController;
use App\Http\Controllers\Admin\PilotRegistrationController as AdminPilotRegistrationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KartDrawController;
use App\Http\Controllers\PilotController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PublicPilotRegistrationController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\StageManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('public.home');
Route::get('/inscreva-se', [PublicPilotRegistrationController::class, 'create'])->name('public.registrations.create');
Route::post('/inscricoes', [PublicPilotRegistrationController::class, 'store'])->name('public.registrations.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/senha', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::resource('temporadas', SeasonController::class)->parameters([
        'temporadas' => 'season',
    ])->names('seasons');

    Route::resource('categorias', CategoryController::class)->parameters([
        'categorias' => 'category',
    ])->except(['show'])->names('categories');

    Route::resource('pilotos', PilotController::class)->parameters([
        'pilotos' => 'pilot',
    ])->names('pilots');

    Route::resource('admin/inscricoes-esportivas', RegistrationController::class)->parameters([
        'inscricoes-esportivas' => 'registration',
    ])->except(['show'])->names('registrations');

    Route::prefix('admin/inscricoes')->name('admin.pilot-registrations.')->group(function (): void {
        Route::get('/', [AdminPilotRegistrationController::class, 'index'])->name('index');
        Route::get('/{pilotRegistration}', [AdminPilotRegistrationController::class, 'show'])->whereNumber('pilotRegistration')->name('show');
        Route::get('/{pilotRegistration}/edit', [AdminPilotRegistrationController::class, 'edit'])->whereNumber('pilotRegistration')->name('edit');
        Route::put('/{pilotRegistration}', [AdminPilotRegistrationController::class, 'update'])->whereNumber('pilotRegistration')->name('update');
        Route::patch('/{pilotRegistration}/payment', [AdminPilotRegistrationController::class, 'updatePayment'])->whereNumber('pilotRegistration')->name('payment');
        Route::patch('/{pilotRegistration}/archive', [AdminPilotRegistrationController::class, 'archive'])->whereNumber('pilotRegistration')->name('archive');
    });

    Route::prefix('admin/inscricoes/categorias')->name('admin.registration-categories.')->group(function (): void {
        Route::get('/', [PilotRegistrationCategoryController::class, 'index'])->name('index');
        Route::get('/nova', [PilotRegistrationCategoryController::class, 'create'])->name('create');
        Route::post('/', [PilotRegistrationCategoryController::class, 'store'])->name('store');
        Route::get('/{pilotRegistrationCategory}/editar', [PilotRegistrationCategoryController::class, 'edit'])->name('edit');
        Route::put('/{pilotRegistrationCategory}', [PilotRegistrationCategoryController::class, 'update'])->name('update');
        Route::patch('/{pilotRegistrationCategory}/status', [PilotRegistrationCategoryController::class, 'updateStatus'])->name('status');
    });

    Route::resource('etapas', StageController::class)->parameters([
        'etapas' => 'stage',
    ])->names('stages');

    Route::get('/etapas/categorias/{stageCategory}/gestao', [StageManagementController::class, 'show'])
        ->name('stage-management.show');
    Route::post('/etapas/categorias/{stageCategory}/participantes', [StageManagementController::class, 'storeEntry'])
        ->name('stage-management.entries.store');
    Route::post('/etapas/categorias/{stageCategory}/tomada', [StageManagementController::class, 'saveQualifying'])
        ->name('stage-management.qualifying.save');
    Route::post('/etapas/categorias/{stageCategory}/troca-kart', [StageManagementController::class, 'saveKartChange'])
        ->name('stage-management.kart-changes.store');
    Route::post('/etapas/categorias/{stageCategory}/corridas/{raceNumber}', [StageManagementController::class, 'saveRaceResults'])
        ->name('stage-management.races.save');
    Route::post('/etapas/categorias/{stageCategory}/pesagens', [StageManagementController::class, 'saveWeighIn'])
        ->name('stage-management.weigh-ins.save');
    Route::post('/etapas/categorias/{stageCategory}/penalidades', [StageManagementController::class, 'addPenalty'])
        ->name('stage-management.penalties.store');
    Route::post('/etapas/categorias/{stageCategory}/ocorrencias', [StageManagementController::class, 'addOccurrence'])
        ->name('stage-management.occurrences.store');
    Route::post('/pontuacoes/{standing}/ajustes', [StageManagementController::class, 'adjustPoints'])
        ->name('stage-management.points.adjust');
    Route::post('/pontuacoes/{standing}/desempate-manual', [StageManagementController::class, 'resolveTie'])
        ->name('stage-management.points.resolve-tie');
    Route::post('/etapas/categorias/{stageCategory}/recalcular', [StageManagementController::class, 'recalculate'])
        ->name('stage-management.recalculate');

    Route::get('/sorteios/{stageCategory}', [KartDrawController::class, 'show'])->name('kart-draws.show');
    Route::put('/sorteios/{stageCategory}/fila', [KartDrawController::class, 'saveQueue'])->name('kart-draws.queue.save');
    Route::post('/sorteios/{stageCategory}', [KartDrawController::class, 'draw'])->name('kart-draws.draw');
    Route::patch('/sorteios/item/{draw}', [KartDrawController::class, 'update'])->name('kart-draws.update');
    Route::post('/sorteios/lotes/{batch}/travar', [KartDrawController::class, 'lock'])->name('kart-draws.lock');
    Route::post('/sorteios/lotes/{batch}/destravar', [KartDrawController::class, 'unlock'])->name('kart-draws.unlock');
    Route::get('/sorteios/lotes/{batch}/csv', [KartDrawController::class, 'exportCsv'])->name('kart-draws.csv');

    Route::get('/classificacoes/etapa/{stageCategory}', [ClassificationController::class, 'stage'])->name('classifications.stage');
    Route::get('/classificacoes/etapa/{stageCategory}/csv', [ClassificationController::class, 'stageCsv'])->name('classifications.stage.csv');
    Route::get('/classificacoes/etapa/{stageCategory}/pdf', [ClassificationController::class, 'stagePdf'])->name('classifications.stage.pdf');
    Route::get('/classificacoes/campeonato/{seasonCategory}', [ClassificationController::class, 'championship'])->name('classifications.championship');
    Route::get('/classificacoes/campeonato/{seasonCategory}/csv', [ClassificationController::class, 'championshipCsv'])->name('classifications.championship.csv');
    Route::get('/classificacoes/campeonato/{seasonCategory}/pdf', [ClassificationController::class, 'championshipPdf'])->name('classifications.championship.pdf');

    Route::get('/configuracoes', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/configuracoes', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/auditoria', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

require __DIR__.'/auth.php';
