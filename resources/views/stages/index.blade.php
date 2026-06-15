@extends('layouts.app')

@section('title', 'Etapas')
@section('subtitle', 'Planejamento, abertura e fechamento das etapas do campeonato.')

@section('content')
    <div class="content-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Etapas cadastradas</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('stages.create') }}" class="btn btn-primary">Nova etapa</a>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Etapa</th>
                        <th>Temporada</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stages as $stage)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $stage->stage_number }}ª - {{ $stage->name }}</div>
                                <div class="small text-muted">{{ $stage->location ?: 'Local não informado' }}</div>
                            </td>
                            <td>{{ $stage->season->name }}</td>
                            <td>{{ $stage->stage_date->format('d/m/Y') }}</td>
                            <td><span class="badge text-bg-secondary">{{ ucfirst($stage->status) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('stages.show', $stage) }}" class="btn btn-sm btn-outline-dark">Abrir</a>
                                <a href="{{ route('stages.edit', $stage) }}" class="btn btn-sm btn-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Nenhuma etapa cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $stages->links() }}</div>
    </div>
@endsection
