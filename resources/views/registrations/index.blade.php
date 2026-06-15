@extends('layouts.app')

@section('title', 'Inscrições')
@section('subtitle', 'Vínculo entre piloto, categoria e temporada. O limite operacional continua valendo por etapa.')

@section('content')
    <div class="content-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Inscrições registradas</div>
            <a href="{{ route('registrations.create') }}" class="btn btn-primary">Nova inscrição</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Piloto</th>
                        <th>Temporada</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrations as $registration)
                        <tr>
                            <td>{{ $registration->pilot->displayName() }}</td>
                            <td>{{ $registration->seasonCategory->season->name }}</td>
                            <td>{{ $registration->seasonCategory->category->name }}</td>
                            <td>{{ strtoupper($registration->registration_type) }}</td>
                            <td>{{ ucfirst($registration->status) }}</td>
                            <td class="text-end">
                                <a href="{{ route('registrations.edit', $registration) }}" class="btn btn-sm btn-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhuma inscrição registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $registrations->links() }}</div>
    </div>

    <div class="content-card p-4">
        <div class="section-title">Resumo por categoria</div>
        <div class="row g-3">
            @foreach ($seasonCategories as $seasonCategory)
                <div class="col-lg-4 col-md-6">
                    <div class="border rounded-4 p-3">
                        <div class="fw-semibold">{{ $seasonCategory->category->name }}</div>
                        <div class="small text-muted">{{ $seasonCategory->season->name }}</div>
                        <div class="mt-2">{{ $seasonCategory->confirmedRegistrationsCount() }} inscrições confirmadas na temporada</div>
                        <div class="small text-muted">Limite operacional: até {{ $seasonCategory->effectivePilotLimit() }} karts por etapa</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
