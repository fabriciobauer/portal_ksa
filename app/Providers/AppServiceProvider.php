<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Pilot;
use App\Models\Setting;
use App\Models\Stage;
use App\Policies\AuditLogPolicy;
use App\Policies\PilotPolicy;
use App\Policies\SettingPolicy;
use App\Policies\StagePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Pilot::class, PilotPolicy::class);
        Gate::policy(Stage::class, StagePolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Gate::define('admin-only', fn ($user) => $user->isAdmin());
        Gate::define('manage-championship', fn ($user) => $user->isStaff());
        Gate::define('manage-points-override', fn ($user) => $user->isAdmin());
        Gate::define('manage-settings', fn ($user) => $user->isAdmin());
        Gate::define('view-audit-log', fn ($user) => $user->isAdmin());

        Paginator::useBootstrapFive();
    }
}
