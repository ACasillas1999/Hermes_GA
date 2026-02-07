@extends('layouts.app')

@section('title', 'Historial completo')
@section('subtitle', 'Todos los envios registrados')

@section('header-actions')
    <a class="button button-secondary" href="{{ route('messaging.index') }}">Volver a envios</a>
@endsection

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('history.index') }}">
            <div class="row">
                <label for="q">Buscar (persona, telefono o plantilla)</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Nombre, telefono o plantilla">
            </div>
            <div class="row-inline">
                <div>
                    <label for="status">Estado</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <option value="sent" @selected($filters['status'] === 'sent')>sent</option>
                        <option value="failed" @selected($filters['status'] === 'failed')>failed</option>
                    </select>
                </div>
                <div>
                    <label for="template">Plantilla</label>
                    <select id="template" name="template">
                        <option value="">Todas</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template }}" @selected($filters['template'] === $template)>{{ $template }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="listado_id">Listado</label>
                    <select id="listado_id" name="listado_id">
                        <option value="">Todos</option>
                        @foreach ($listados as $listado)
                            <option value="{{ $listado->id }}" @selected($filters['listado_id'] == $listado->id)>{{ $listado->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="from">Desde</label>
                    <input id="from" type="date" name="from" value="{{ $filters['from'] }}">
                </div>
                <div>
                    <label for="to">Hasta</label>
                    <input id="to" type="date" name="to" value="{{ $filters['to'] }}">
                </div>
            </div>
            <div class="row-inline">
                <button class="button" type="submit">Filtrar</button>
                <a class="button button-secondary" href="{{ route('history.index') }}">Limpiar</a>
            </div>
        </form>
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
            {{ $logs->links('components.pagination') }}
        </div>
    </div>
@endsection
