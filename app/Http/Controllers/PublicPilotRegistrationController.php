<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicPilotRegistrationRequest;
use App\Models\PilotRegistration;
use App\Models\PilotRegistrationCategory;
use App\Services\PilotRegistrationService;
use App\Support\Analytics;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicPilotRegistrationController extends Controller
{
    public function create(): View
    {
        $categories = PilotRegistrationCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.registrations.create', [
            'registration' => new PilotRegistration(),
            'categories' => $categories,
            'analyticsPageEvent' => Analytics::pageEvent('registration_form_view', [
                'page_type' => 'public_registration',
                'section' => 'form',
            ]),
        ]);
    }

    public function store(
        PublicPilotRegistrationRequest $request,
        PilotRegistrationService $pilotRegistrationService,
    ): RedirectResponse {
        $registration = $pilotRegistrationService->createFromPublic($request->validated());

        Analytics::flash('registration_submit_success', [
            'page_type' => 'public_registration',
            'section' => 'form',
            'registration_category' => $registration->category?->slug,
            'registration_status' => 'unpaid',
        ]);

        return redirect()
            ->route('public.registrations.create')
            ->with('status', 'Inscrição enviada com sucesso. Aguarde o contato da organização para a confirmação do pagamento.');
    }
}
