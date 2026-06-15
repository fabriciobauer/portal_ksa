@extends('layouts.app')

@section('title', 'Log de Auditoria')
@section('subtitle', 'Histórico auditável das alterações sensíveis do sistema.')

@section('content')
    <div class="content-card p-4">
        <div class="section-title">Eventos recentes</div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Data</th><th>Usuário</th><th>Ação</th><th>Registro</th><th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->user?->name ?: 'Sistema' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Nenhum evento de auditoria registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
@endsection
