@extends('layouts.app')

@section('title', 'Inscrições de pilotos')
@section('subtitle', 'Fluxo público com pagamento, auditoria e sincronização para o cadastro de pilotos.')

@section('content')
    <div class="card p-4 space-y-4">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <div class="section-title mb-1">Gestão de inscrições</div>
                <div class="text-sm text-ksa-muted">Busca, filtros por pagamento e ações rápidas por card.</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('public.registrations.create') }}" class="btn-outline btn-sm" target="_blank" rel="noopener">Abrir formulário público</a>
                <a href="{{ route('admin.registration-categories.index') }}" class="btn-primary btn-sm">Categorias do formulário</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.pilot-registrations.index') }}" class="grid md:grid-cols-3 gap-3 items-end">
            <div class="field md:col-span-2">
                <label for="search" class="form-label">Busca</label>
                <input id="search" type="text" name="search" value="{{ $search }}" class="form-input" placeholder="Nome, WhatsApp, CPF ou e-mail">
            </div>
            <div class="field">
                <label class="form-label">Filtro por pagamento</label>
                <div class="flex gap-1">
                    @foreach(['all' => 'Todos', 'paid' => 'Pagos', 'unpaid' => 'Não pagos'] as $val => $label)
                        <a href="{{ route('admin.pilot-registrations.index', ['status' => $val, 'search' => $search]) }}"
                           class="{{ $status === $val ? 'btn-primary' : 'btn-outline' }} btn-sm flex-1 text-center">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <button class="btn-outline btn-sm md:col-start-3">Filtrar</button>
        </form>
    </div>

    @if ($registrations->isEmpty())
        <div class="card p-8 text-center text-sm text-ksa-muted">
            Nenhuma inscrição encontrada para os filtros informados.
        </div>
    @else
        <div class="grid md:grid-cols-2 gap-4">
            @foreach ($registrations as $registration)
                <div class="card p-4 space-y-3 {{ $registration->payment_status ? 'border-l-4 border-l-ksa-green' : 'border-l-4 border-l-ksa-red' }}">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <div class="font-bold text-base">{{ $registration->full_name }}</div>
                            <div class="text-xs text-ksa-muted">Inscrito em {{ $registration->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="{{ $registration->payment_status ? 'badge-green' : 'badge-red' }}">{{ $registration->payment_status ? 'Pago' : 'Não pago' }}</span>
                            <span class="badge-gray">{{ $registration->category->name }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div><span class="text-ksa-muted text-xs">WhatsApp</span><br>{{ $registration->whatsapp }}</div>
                        <div><span class="text-ksa-muted text-xs">CPF</span><br>{{ $registration->cpf }}</div>
                        <div><span class="text-ksa-muted text-xs">E-mail</span><br>{{ $registration->email }}</div>
                        <div><span class="text-ksa-muted text-xs">Idade / peso</span><br>{{ $registration->age }} anos · {{ number_format((float) $registration->weight_kg, 2, ',', '.') }} kg</div>
                        <div><span class="text-ksa-muted text-xs">Exp. kart</span><br>{{ $registration->has_kart_experience ? 'Sim' : 'Não' }}</div>
                        <div><span class="text-ksa-muted text-xs">Exp. campeonato</span><br>{{ $registration->has_championship_experience ? 'Sim' : 'Não' }}</div>
                        <div class="col-span-2"><span class="text-ksa-muted text-xs">Endereço</span><br>{{ $registration->address }}</div>
                        <div class="col-span-2">
                            <span class="text-ksa-muted text-xs">Piloto sincronizado</span><br>
                            @if ($registration->pilot)
                                <a href="{{ route('pilots.show', $registration->pilot) }}" class="text-ksa-navy font-semibold">{{ $registration->pilot->displayName() }}</a>
                            @else
                                <span class="text-ksa-muted">Ainda não sincronizado</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2 border-t border-ksa-border">
                        <form method="POST" action="{{ route('admin.pilot-registrations.payment', $registration) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="payment_status" value="{{ $registration->payment_status ? '0' : '1' }}">
                            <button type="submit" class="{{ $registration->payment_status ? 'btn-danger' : 'btn-success' }} btn-sm">
                                {{ $registration->payment_status ? 'Desmarcar pago' : 'Marcar como pago' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.pilot-registrations.show', $registration) }}" class="btn-outline btn-sm">Ver detalhes</a>
                        <a href="{{ route('admin.pilot-registrations.edit', $registration) }}" class="btn-primary btn-sm">Editar</a>
                        <form method="POST" action="{{ route('admin.pilot-registrations.archive', $registration) }}" onsubmit="return confirm('Arquivar esta inscrição?');">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-ghost btn-sm">Arquivar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if($registrations->hasPages())
            <div class="p-4">{{ $registrations->links() }}</div>
        @endif
    @endif
@endsection
