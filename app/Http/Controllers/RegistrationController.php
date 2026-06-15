<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Models\Pilot;
use App\Models\SeasonCategory;
use App\Models\SeasonCategoryRegistration;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(protected RegistrationService $registrationService)
    {
    }

    public function index(): View
    {
        Gate::authorize('manage-championship');

        $registrations = SeasonCategoryRegistration::query()
            ->with(['pilot', 'seasonCategory.season', 'seasonCategory.category'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $seasonCategories = SeasonCategory::query()->with(['season', 'category'])->orderByDesc('season_id')->get();

        return view('registrations.index', compact('registrations', 'seasonCategories'));
    }

    public function create(): View
    {
        Gate::authorize('manage-championship');

        return view('registrations.form', [
            'registration' => new SeasonCategoryRegistration(),
            'seasonCategories' => SeasonCategory::query()->with(['season', 'category'])->orderByDesc('season_id')->get(),
            'pilots' => Pilot::query()->orderBy('name')->get(),
        ]);
    }

    public function store(RegistrationRequest $request): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->registrationService->ensureSingleEntriesAllowed($request->string('registration_type')->toString());
        $createdCount = $this->registrationService->createMany($request->validated());

        return redirect()->route('registrations.index')->with(
            'status',
            $createdCount === 1
                ? 'Inscrição criada com sucesso.'
                : "{$createdCount} inscrições criadas com sucesso.",
        );
    }

    public function edit(SeasonCategoryRegistration $registration): View
    {
        Gate::authorize('manage-championship');

        return view('registrations.form', [
            'registration' => $registration,
            'seasonCategories' => SeasonCategory::query()->with(['season', 'category'])->orderByDesc('season_id')->get(),
            'pilots' => Pilot::query()->orderBy('name')->get(),
        ]);
    }

    public function update(RegistrationRequest $request, SeasonCategoryRegistration $registration): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->registrationService->ensureSingleEntriesAllowed($request->string('registration_type')->toString());

        $registration->update($request->validated());
        $this->registrationService->refreshStatuses($registration->seasonCategory);

        return back()->with('status', 'Inscrição atualizada com sucesso.');
    }

    public function destroy(SeasonCategoryRegistration $registration): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $registration->update(['status' => SeasonCategoryRegistration::STATUS_CANCELLED]);
        $this->registrationService->refreshStatuses($registration->seasonCategory);

        return redirect()->route('registrations.index')->with('status', 'Inscrição cancelada com sucesso.');
    }
}
