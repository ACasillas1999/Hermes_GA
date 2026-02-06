@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Resumen general de mensajeria')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        margin-bottom: 20px;
    }

    .stat-card {
        display: grid;
        gap: 8px;
    }

    .stat-label {
        color: var(--muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
    }

    .calendar {
        display: grid;
        gap: 8px;
    }

    .calendar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 6px;
    }

    .calendar-cell {
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 10px;
        padding: 8px;
        min-height: 64px;
        display: grid;
        gap: 6px;
        background: rgba(15, 23, 42, 0.4);
        text-decoration: none;
        color: inherit;
    }

    .calendar-cell.dim {
        opacity: 0.45;
    }

    .calendar-cell.active {
        border-color: rgba(56, 189, 248, 0.6);
        box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.25);
    }

    .calendar-day {
        font-size: 12px;
        font-weight: 600;
    }

    .calendar-count {
        font-size: 11px;
        color: var(--muted);
    }

    .weekday {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        text-align: center;
    }

    .tabbar {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .tab {
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(15, 23, 42, 0.4);
        font-size: 12px;
        text-decoration: none;
        color: var(--text);
    }

    .tab.active {
        border-color: rgba(56, 189, 248, 0.6);
        box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.2);
        background: rgba(56, 189, 248, 0.12);
    }
</style>
@endpush

@section('content')
    <div class="stats-grid">
        <div class="card stat-card">
            <div class="stat-label">Plantillas</div>
            <div class="stat-value">{{ $stats['plantillas'] }}</div>
            <div class="muted">Registradas en Meta</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Listados</div>
            <div class="stat-value">{{ $stats['listados'] }}</div>
            <div class="muted">Grupos disponibles</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Personas</div>
            <div class="stat-value">{{ $stats['empleados'] }}</div>
            <div class="muted">Contactos cargados</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Mensajes</div>
            <div class="stat-value">{{ $stats['mensajes'] }}</div>
            <div class="muted">Registros enviados</div>
        </div>
    </div>

    <div class="tabbar">
        <a class="tab {{ $viewMode === 'calendar' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['monthValue']]) }}">Calendario</a>
        <a class="tab {{ $viewMode === 'history' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'history']) }}">Historial reciente</a>
    </div>

    @if ($viewMode === 'history')
        <section class="card">
            <div class="row-inline" style="justify-content: space-between;">
                <h2>Historial reciente</h2>
                <a class="button button-secondary" href="{{ route('history.index') }}">Ver historial completo</a>
            </div>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Persona</th>
                            <th>Listado</th>
                            <th>Plantilla</th>
                            <th>Estado</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentMessages as $log)
                            <tr>
                                <td>{{ optional($log->sent_at)->format('Y-m-d H:i') ?? $log->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $log->empleado?->Nombre ?? 'Sin nombre' }}</td>
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
                                <td colspan="6">No hay mensajes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="card">
            <div class="calendar">
                <div class="calendar-header">
                    <div>
                        <h2 style="margin:0;">Calendario de envios</h2>
                        <div class="muted">{{ $calendar['monthLabel'] }}</div>
                    </div>
                    <div class="row-inline">
                        <a class="button button-secondary" href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['prevMonth']]) }}">Anterior</a>
                        <a class="button button-secondary" href="{{ route('dashboard', ['view' => 'calendar']) }}">Hoy</a>
                        <a class="button button-secondary" href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['nextMonth']]) }}">Siguiente</a>
                    </div>
                </div>
                <div class="calendar-grid" style="margin-top: 8px;">
                    @foreach (['Lun','Mar','Mie','Jue','Vie','Sab','Dom'] as $dayName)
                        <div class="weekday">{{ $dayName }}</div>
                    @endforeach
                    @foreach ($calendar['weeks'] as $week)
                        @foreach ($week as $day)
                            <a
                                class="calendar-cell {{ $day['inMonth'] ? '' : 'dim' }} {{ $selectedDate === $day['date'] ? 'active' : '' }}"
                                href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['monthValue'], 'date' => $day['date']]) }}"
                            >
                                <div class="calendar-day">{{ $day['label'] }}</div>
                                <div class="calendar-count">{{ $day['count'] }} envios</div>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>

        @if ($selectedDate)
            <section class="card" style="margin-top: 14px;">
                <div class="row-inline" style="justify-content: space-between;">
                    <h2>Detalle del {{ $selectedDate }}</h2>
                    <a class="button button-secondary" href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['monthValue']]) }}">Cerrar detalle</a>
                </div>
                <div style="overflow-x: auto; margin-top: 8px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Persona</th>
                                <th>Telefono</th>
                                <th>Listado</th>
                                <th>Plantilla</th>
                                <th>Estado</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dayLogs as $log)
                                <tr>
                                    <td>{{ optional($log->sent_at)->format('H:i') ?? $log->created_at->format('H:i') }}</td>
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
                                    <td colspan="7">No hay envios en este dia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif
@endsection
