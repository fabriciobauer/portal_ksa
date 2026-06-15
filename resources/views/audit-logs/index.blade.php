@extends('layouts.app')

@section('title', 'Auditoria')
@section('subtitle', 'Histórico auditável das alterações do sistema.')

@section('content')
    <div class="card">
        <div class="p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Eventos recentes</div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Registro</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="font-semibold text-sm">{{ $log->user?->name ?: 'Sistema' }}</td>
                            <td><span class="badge-gray">{{ $log->action }}</span></td>
                            <td class="text-xs text-ksa-muted">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td class="text-sm">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-ksa-muted py-8">Nenhum evento registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-4 border-t border-ksa-border">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
