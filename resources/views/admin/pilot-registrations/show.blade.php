@extends('layouts.app')

@section('title', 'Detalhes da inscrição')
@section('subtitle', 'Histórico, vínculo com piloto e rastreabilidade administrativa.')

@section('content')
    <div class="content-card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="section-title mb-1">{{ $registration->full_name }}</div>
                <div class="text-muted">Inscrição criada em {{ $registration->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $registration->payment_status ? 'text-bg-success' : 'text-bg-danger' }}">
                    {{ $registration->payment_status ? 'Pago' : 'Não pago' }}
                </span>
                <a href="{{ route('admin.pilot-registrations.edit', $registration) }}" class="btn btn-primary">Editar inscrição</a>
                <a href="{{ route('admin.pilot-registrations.index') }}" class="btn btn-outline-dark">Voltar</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                    <div class="section-title mb-3">Dados do inscrito</div>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">WhatsApp</dt>
                        <dd class="col-sm-8">{{ $registration->whatsapp }}</dd>
                        <dt class="col-sm-4">CPF</dt>
                        <dd class="col-sm-8">{{ $registration->cpf }}</dd>
                        <dt class="col-sm-4">E-mail</dt>
                        <dd class="col-sm-8">{{ $registration->email }}</dd>
                        <dt class="col-sm-4">Endereço</dt>
                        <dd class="col-sm-8">{{ $registration->address }}</dd>
                        <dt class="col-sm-4">Kart</dt>
                        <dd class="col-sm-8">{{ $registration->has_kart_experience ? 'Sim' : 'Não' }}</dd>
                        <dt class="col-sm-4">Campeonato</dt>
                        <dd class="col-sm-8">{{ $registration->has_championship_experience ? 'Sim' : 'Não' }}</dd>
                        <dt class="col-sm-4">Peso</dt>
                        <dd class="col-sm-8">{{ number_format((float) $registration->weight_kg, 2, ',', '.') }} kg</dd>
                        <dt class="col-sm-4">Idade</dt>
                        <dd class="col-sm-8">{{ $registration->age }} anos</dd>
                        <dt class="col-sm-4">Categoria</dt>
                        <dd class="col-sm-8">{{ $registration->category->name }}</dd>
                    </dl>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                    <div class="section-title mb-3">Administração e conversão</div>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-5">Pago em</dt>
                        <dd class="col-sm-7">{{ $registration->paid_at?->format('d/m/Y H:i') ?? 'Ainda não marcado' }}</dd>
                        <dt class="col-sm-5">Marcado por</dt>
                        <dd class="col-sm-7">{{ $registration->paymentMarkedBy?->name ?? '-' }}</dd>
                        <dt class="col-sm-5">Desmarcado por</dt>
                        <dd class="col-sm-7">{{ $registration->paymentUnmarkedBy?->name ?? '-' }}</dd>
                        <dt class="col-sm-5">Convertido em piloto em</dt>
                        <dd class="col-sm-7">{{ $registration->converted_to_pilot_at?->format('d/m/Y H:i') ?? 'Ainda não convertido' }}</dd>
                        <dt class="col-sm-5">Piloto vinculado</dt>
                        <dd class="col-sm-7">
                            @if ($registration->pilot)
                                <a href="{{ route('pilots.show', $registration->pilot) }}" class="text-decoration-none">{{ $registration->pilot->displayName() }}</a>
                            @else
                                Nenhum piloto vinculado
                            @endif
                        </dd>
                        <dt class="col-sm-5">Criado por</dt>
                        <dd class="col-sm-7">{{ $registration->createdBy?->name ?? 'Formulário público' }}</dd>
                        <dt class="col-sm-5">Última edição</dt>
                        <dd class="col-sm-7">{{ $registration->updatedBy?->name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        @if ($registration->notes)
            <div class="border rounded-4 p-3 mt-4">
                <div class="section-title mb-2">Observações</div>
                <div class="small">{{ $registration->notes }}</div>
            </div>
        @endif
    </div>

    <div class="content-card p-4">
        <div class="section-title">Histórico de auditoria</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
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
                            <td>{{ $auditLog->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $auditLog->action }}</td>
                            <td>{{ $auditLog->description ?: '-' }}</td>
                            <td>{{ $auditLog->user?->name ?? 'Sistema' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhum evento de auditoria registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
