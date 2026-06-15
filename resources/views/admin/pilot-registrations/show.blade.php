@extends('layouts.app')

@section('title', 'Detalhes da inscrição')
@section('subtitle', 'Histórico, vínculo com piloto e rastreabilidade administrativa.')

@section('content')
    <div class="card p-4 space-y-4">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <div class="font-bold text-lg">{{ $registration->full_name }}</div>
                <div class="text-sm text-ksa-muted">Inscrição criada em {{ $registration->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="{{ $registration->payment_status ? 'badge-green' : 'badge-red' }}">{{ $registration->payment_status ? 'Pago' : 'Não pago' }}</span>
                <a href="{{ route('admin.pilot-registrations.edit', $registration) }}" class="btn-primary btn-sm">Editar inscrição</a>
                <a href="{{ route('admin.pilot-registrations.index') }}" class="btn-outline btn-sm">Voltar</a>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="border border-ksa-border rounded-xl p-4">
                <div class="section-title mb-3">Dados do inscrito</div>
                <dl class="space-y-2 text-sm">
                    @foreach([
                        'WhatsApp' => $registration->whatsapp,
                        'CPF' => $registration->cpf,
                        'E-mail' => $registration->email,
                        'Endereço' => $registration->address,
                        'Exp. kart' => $registration->has_kart_experience ? 'Sim' : 'Não',
                        'Exp. campeonato' => $registration->has_championship_experience ? 'Sim' : 'Não',
                        'Peso' => number_format((float) $registration->weight_kg, 2, ',', '.') . ' kg',
                        'Idade' => $registration->age . ' anos',
                        'Categoria' => $registration->category->name,
                    ] as $label => $value)
                        <div class="flex gap-2">
                            <dt class="w-36 shrink-0 text-ksa-muted">{{ $label }}</dt>
                            <dd class="font-semibold">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="border border-ksa-border rounded-xl p-4">
                <div class="section-title mb-3">Administração e conversão</div>
                <dl class="space-y-2 text-sm">
                    <div class="flex gap-2">
                        <dt class="w-40 shrink-0 text-ksa-muted">Pago em</dt>
                        <dd class="font-semibold">{{ $registration->paid_at?->format('d/m/Y H:i') ?? 'Ainda não marcado' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 shrink-0 text-ksa-muted">Marcado por</dt>
                        <dd class="font-semibold">{{ $registration->paymentMarkedBy?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 shrink-0 text-ksa-muted">Desmarcado por</dt>
                        <dd class="font-semibold">{{ $registration->paymentUnmarkedBy?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 shrink-0 text-ksa-muted">Convertido em piloto em</dt>
                        <dd class="font-semibold">{{ $registration->converted_to_pilot_at?->format('d/m/Y H:i') ?? 'Ainda não convertido' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 shrink-0 text-ksa-muted">Piloto vinculado</dt>
                        <dd class="font-semibold">
                            @if ($registration->pilot)
                                <a href="{{ route('pilots.show', $registration->pilot) }}" class="text-ksa-navy hover:underline">{{ $registration->pilot->displayName() }}</a>
                            @else
                                <span class="text-ksa-muted">Nenhum piloto vinculado</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 shrink-0 text-ksa-muted">Criado por</dt>
                        <dd class="font-semibold">{{ $registration->createdBy?->name ?? 'Formulário público' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 shrink-0 text-ksa-muted">Última edição</dt>
                        <dd class="font-semibold">{{ $registration->updatedBy?->name ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if ($registration->notes)
            <div class="border border-ksa-border rounded-xl p-4">
                <div class="section-title mb-2">Observações</div>
                <p class="text-sm">{{ $registration->notes }}</p>
            </div>
        @endif
    </div>

    <div class="card p-4 space-y-3">
        <div class="section-title">Histórico de auditoria</div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                        <th>Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registration->auditLogs as $auditLog)
                        <tr>
                            <td class="text-xs whitespace-nowrap">{{ $auditLog->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge-gray">{{ $auditLog->action }}</span></td>
                            <td class="text-sm">{{ $auditLog->description ?: '-' }}</td>
                            <td class="font-semibold text-sm">{{ $auditLog->user?->name ?? 'Sistema' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-ksa-muted py-8">Nenhum evento de auditoria registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
