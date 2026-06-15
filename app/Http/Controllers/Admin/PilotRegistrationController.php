<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPilotRegistrationRequest;
use App\Http\Requests\PilotRegistrationPaymentRequest;
use App\Models\PilotRegistration;
use App\Models\PilotRegistrationCategory;
use App\Services\PilotRegistrationService;
use App\Support\Analytics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PilotRegistrationController extends Controller
{
    public function __construct(protected PilotRegistrationService $pilotRegistrationService)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('manage-championship');

        $status = $request->string('status')->toString();
        $status = in_array($status, ['paid', 'unpaid'], true) ? $status : 'all';
        $search = trim((string) $request->input('search'));
        $searchDigits = preg_replace('/\D+/', '', $search) ?: '';

        $registrations = PilotRegistration::query()
            ->with(['category', 'pilot'])
            ->where('is_archived', false)
            ->when($status === 'paid', fn ($query) => $query->where('payment_status', true))
            ->when($status === 'unpaid', fn ($query) => $query->where('payment_status', false))
            ->when($search !== '', function ($query) use ($search, $searchDigits): void {
                $query->where(function ($innerQuery) use ($search, $searchDigits): void {
                    $innerQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    if ($searchDigits !== '') {
                        $innerQuery
                            ->orWhere('whatsapp', 'like', "%{$searchDigits}%")
                            ->orWhere('cpf', 'like', "%{$searchDigits}%");
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.pilot-registrations.index', [
            'registrations' => $registrations,
            'status' => $status,
            'search' => $search,
            'analyticsPageEvent' => Analytics::pageEvent('admin_registrations_view', [
                'page_type' => 'admin_registrations',
                'section' => 'listing',
            ]),
        ]);
    }

    public function show(PilotRegistration $pilotRegistration): View
    {
        Gate::authorize('manage-championship');

        $pilotRegistration->load([
            'category',
            'pilot',
            'createdBy',
            'updatedBy',
            'paymentMarkedBy',
            'paymentUnmarkedBy',
            'archivedBy',
            'auditLogs' => fn ($query) => $query->with('user')->latest(),
        ]);

        return view('admin.pilot-registrations.show', [
            'registration' => $pilotRegistration,
        ]);
    }

    public function edit(PilotRegistration $pilotRegistration): View
    {
        Gate::authorize('manage-championship');

        return view('admin.pilot-registrations.form', [
            'registration' => $pilotRegistration,
            'categories' => PilotRegistrationCategory::query()
                ->orderByDesc('is_active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(
        AdminPilotRegistrationRequest $request,
        PilotRegistration $pilotRegistration,
    ): RedirectResponse {
        Gate::authorize('manage-championship');

        $this->pilotRegistrationService->update(
            $pilotRegistration,
            $request->validated(),
            $request->user(),
        );

        return back()->with('status', 'Inscrição atualizada com sucesso.');
    }

    public function updatePayment(
        PilotRegistrationPaymentRequest $request,
        PilotRegistration $pilotRegistration,
    ): RedirectResponse {
        Gate::authorize('manage-championship');

        $paymentStatus = $request->boolean('payment_status');
        $result = $this->pilotRegistrationService->setPaymentStatus(
            $pilotRegistration,
            $paymentStatus,
            $request->user(),
        );
        $registration = $result['registration'];

        if ($paymentStatus && $result['payment_changed']) {
            Analytics::flash('registration_payment_marked', [
                'page_type' => 'admin_registrations',
                'section' => 'card',
                'registration_category' => $registration->category?->slug,
                'registration_status' => 'paid',
            ]);
        }

        if (! $paymentStatus && $result['payment_changed']) {
            Analytics::flash('registration_payment_unmarked', [
                'page_type' => 'admin_registrations',
                'section' => 'card',
                'registration_category' => $registration->category?->slug,
                'registration_status' => 'unpaid',
            ]);
        }

        if ($result['converted_to_pilot']) {
            Analytics::flash('registration_converted_to_pilot', [
                'page_type' => 'admin_registrations',
                'section' => 'card',
                'registration_category' => $registration->category?->slug,
                'registration_status' => $registration->payment_status ? 'paid' : 'unpaid',
            ]);
        }

        return back()->with('status', match (true) {
            $paymentStatus && $result['payment_changed'] => 'Pagamento marcado e piloto sincronizado com sucesso.',
            $paymentStatus => 'Esta inscrição já estava marcada como paga.',
            ! $paymentStatus && $result['payment_changed'] => 'Pagamento desmarcado com sucesso. O piloto vinculado foi mantido.',
            default => 'Esta inscrição já estava marcada como não paga.',
        });
    }

    public function archive(Request $request, PilotRegistration $pilotRegistration): RedirectResponse
    {
        Gate::authorize('manage-championship');

        $this->pilotRegistrationService->archive($pilotRegistration, $request->user());

        return redirect()
            ->route('admin.pilot-registrations.index')
            ->with('status', 'Inscrição arquivada com sucesso.');
    }
}
