@extends('layouts.app')

@section('title', 'Inscrições de pilotos')
@section('subtitle', 'Fluxo público com pagamento, auditoria e sincronização para o cadastro de pilotos.')

@section('content')
    <div class="content-card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="section-title mb-1">Gestão de inscrições</div>
                <div class="text-muted">Busca, filtros por pagamento e ações rápidas por card.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('public.registrations.create') }}" class="btn btn-outline-dark" target="_blank" rel="noopener">Abrir formulário público</a>
                <a href="{{ route('admin.registration-categories.index') }}" class="btn btn-primary">Categorias do formulário</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.pilot-registrations.index') }}" class="row g-3 align-items-end mb-4">
            <div class="col-lg-6">
                <label for="search" class="form-label">Busca</label>
                <input id="search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Nome, WhatsApp, CPF ou e-mail">
            </div>
            <div class="col-lg-4">
                <label class="form-label d-block">Filtro por pagamento</label>
                <div class="nav nav-pills gap-2">
                    <a href="{{ route('admin.pilot-registrations.index', ['status' => 'all', 'search' => $search]) }}" class="nav-link {{ $status === 'all' ? 'active' : '' }}">Todos</a>
                    <a href="{{ route('admin.pilot-registrations.index', ['status' => 'paid', 'search' => $search]) }}" class="nav-link {{ $status === 'paid' ? 'active' : '' }}">Pagos</a>
                    <a href="{{ route('admin.pilot-registrations.index', ['status' => 'unpaid', 'search' => $search]) }}" class="nav-link {{ $status === 'unpaid' ? 'active' : '' }}">Não pagos</a>
                </div>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-outline-dark">Filtrar</button>
            </div>
        </form>
    </div>

    @if ($registrations->isEmpty())
        <div class="content-card p-4 text-center text-muted">
            Nenhuma inscrição encontrada para os filtros informados.
        </div>
    @else
        <div class="row g-4">
            @foreach ($registrations as $registration)
                <div class="col-xl-6">
                    <div class="registration-admin-card {{ $registration->payment_status ? 'registration-admin-card-paid' : 'registration-admin-card-unpaid' }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="registration-card-title">{{ $registration->full_name }}</div>
                                <div class="text-muted small">
                                    Inscrito em {{ $registration->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge {{ $registration->payment_status ? 'text-bg-success' : 'text-bg-danger' }}">
                                    {{ $registration->payment_status ? 'Pago' : 'Não pago' }}
                                </span>
                                <span class="badge text-bg-secondary">{{ $registration->category->name }}</span>
                            </div>
                        </div>

                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <div class="registration-data-label">WhatsApp</div>
                                <div class="registration-data-value">{{ $registration->whatsapp }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="registration-data-label">CPF</div>
                                <div class="registration-data-value">{{ $registration->cpf }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="registration-data-label">E-mail</div>
                                <div class="registration-data-value">{{ $registration->email }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="registration-data-label">Idade / peso</div>
                                <div class="registration-data-value">{{ $registration->age }} anos · {{ number_format((float) $registration->weight_kg, 2, ',', '.') }} kg</div>
                            </div>
                            <div class="col-md-6">
                                <div class="registration-data-label">Experiência em kart</div>
                                <div class="registration-data-value">{{ $registration->has_kart_experience ? 'Sim' : 'Não' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="registration-data-label">Experiência em campeonato</div>
                                <div class="registration-data-value">{{ $registration->has_championship_experience ? 'Sim' : 'Não' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="registration-data-label">Endereço</div>
                                <div class="registration-data-value">{{ $registration->address }}</div>
                            </div>
                            <div class="col-12">
                                <div class="registration-data-label">Piloto sincronizado</div>
                                <div class="registration-data-value">
                                    @if ($registration->pilot)
                                        <a href="{{ route('pilots.show', $registration->pilot) }}" class="text-decoration-none">
                                            {{ $registration->pilot->displayName() }}
                                        </a>
                                    @else
                                        Ainda não sincronizado
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <form method="POST" action="{{ route('admin.pilot-registrations.payment', $registration) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="payment_status" value="{{ $registration->payment_status ? '0' : '1' }}">
                                <button type="submit" class="btn {{ $registration->payment_status ? 'btn-outline-danger' : 'btn-success' }}">
                                    {{ $registration->payment_status ? 'Desmarcar pago' : 'Marcar como pago' }}
                                </button>
                            </form>

                            <a href="{{ route('admin.pilot-registrations.show', $registration) }}" class="btn btn-outline-dark">Ver detalhes</a>
                            <a href="{{ route('admin.pilot-registrations.edit', $registration) }}" class="btn btn-primary">Editar inscrição</a>

                            <form method="POST" action="{{ route('admin.pilot-registrations.archive', $registration) }}" onsubmit="return confirm('Arquivar esta inscrição?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-secondary">Arquivar</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $registrations->links() }}</div>
    @endif
@endsection
