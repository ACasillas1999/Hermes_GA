@extends('layouts.app')

@section('title', 'Detalle de envio programado')
@section('subtitle', 'Registro por persona del envio')

@section('header-actions')
    <a class="button button-secondary" href="{{ route('scheduled.index') }}">Volver</a>
@endsection

@section('content')
    @if (!empty($usedFallback))
        <div class="alert alert-success">
            Se detectaron registros previos sin vinculacion y se asociaron automaticamente a este envio ({{ $linkedCount }}).
        </div>
    @endif
    <div class="card">
        <div class="row-inline" style="justify-content: space-between;">
            <div>
                <div class="muted">Listado</div>
                <div style="font-weight: 600;">{{ $scheduledMessage->listado?->nombre ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="muted">Plantilla</div>
                <div style="font-weight: 600;">{{ $scheduledMessage->template?->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="muted">Programado</div>
                <div style="font-weight: 600;">{{ optional($scheduledMessage->scheduled_at)->format('Y-m-d H:i') }}</div>
            </div>
            <div>
                <div class="muted">Estado</div>
                <div style="font-weight: 600;">{{ $scheduledMessage->status }}</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 14px;">
        <div class="row-inline" style="justify-content: space-between;">
            <div class="row-inline">
                <span class="badge">Enviados: {{ $summary['sent'] }}</span>
                <span class="badge">Fallidos: {{ $summary['failed'] }}</span>
            </div>
            @if ($scheduledMessage->error)
                <span style="color:#fecaca;font-size:12px;">{{ $scheduledMessage->error }}</span>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 14px;">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Persona</th>
                        <th>Telefono</th>
                        <th>Listado</th>
                        <th>Plantilla</th>
                        <th>Estado</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ optional($log->sent_at)->format('Y-m-d H:i') ?? $log->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->empleado?->Nombre ?? 'Sin nombre' }}</td>
                            <td>{{ $log->empleado?->Numero ?? 'N/A' }}</td>
                            <td>{{ $log->empleado?->listado?->nombre ?? 'N/A' }}</td>
                            <td>{{ $log->template_name }} ({{ $log->template_language ?? 'N/A' }})</td>
                            <td>{{ $log->status }}</td>
                            <td>
                                @php
                                    $errorMessage = $log->error ?? data_get($log->response, 'error.message');
                                @endphp
                                @if ($log->status === 'failed' && $errorMessage)
                                    <span style="color:#fecaca;font-size:12px;" title="{{ $errorMessage }}">{{ $errorMessage }}</span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 12px;">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
