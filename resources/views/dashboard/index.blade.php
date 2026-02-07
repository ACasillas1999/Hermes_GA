@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Resumen general de mensajería')

@push('styles')
<style>
    /* Override base styles with Divine design */
    body {
        background: #060b18 !important;
    }

    .content {
        background: #060b18;
    }

    .page-header {
        background: rgba(10, 16, 31, 0.5);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }



    .stats-grid {
        display: grid;
        gap: 24px;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        margin-bottom: 40px;
    }

    .stat-card {
        background: #0e1629;
        border: 1px solid rgba(255, 140, 0, 0.2);
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        border-color: rgba(255, 140, 0, 0.4);
    }

    .stat-card.featured {
        border-color: rgba(255, 140, 0, 0.3);
        box-shadow: 0 0 20px rgba(255, 140, 0, 0.15);
    }

    .stat-card.featured::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(255, 140, 0, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-label {
        color: #94a3b8;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
        margin-bottom: 16px;
        position: relative;
        z-index: 1;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: white;
        position: relative;
        z-index: 1;
    }

    .stat-meta {
        color: #94a3b8;
        font-size: 13px;
        margin-top: 8px;
        position: relative;
        z-index: 1;
    }

    .calendar-card {
        background: #0e1629;
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }

    .calendar-header {
        background: linear-gradient(90deg, #0066B3 0%, #FF8C00 100%);
        padding: 24px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .calendar-header h4 {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    .calendar-month-badge {
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 999px;
        backdrop-filter: blur(8px);
    }

    .calendar-nav {
        display: flex;
        gap: 8px;
    }

    .calendar-nav button,
    .calendar-nav a {
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .calendar-nav button:hover,
    .calendar-nav a:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .calendar-body {
        background: #0a101f;
        padding: 0;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0;
    }

    .calendar-weekday {
        padding: 16px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #64748b;
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .calendar-day {
        border: 1px solid rgba(255, 255, 255, 0.05);
        padding: 16px;
        min-height: 100px;
        transition: all 0.2s;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .calendar-day:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .calendar-day.dim {
        opacity: 0.3;
    }

    .calendar-day.active {
        background: rgba(255, 140, 0, 0.05);
        border-color: rgba(255, 140, 0, 0.3);
    }

    .calendar-day-number {
        font-size: 14px;
        font-weight: 700;
        color: white;
        margin-bottom: 8px;
    }

    .calendar-day-count {
        font-size: 10px;
        color: #64748b;
    }

    .calendar-day.has-events .calendar-day-number {
        color: #FF9900;
    }

    .calendar-day.has-events .calendar-day-count {
        color: #FF9900;
        font-weight: 700;
    }

    .tabbar {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
    }

    .tab {
        padding: 10px 20px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(15, 23, 42, 0.4);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #e2e8f0;
        transition: all 0.2s;
    }

    .tab.active {
        border-color: rgba(255, 140, 0, 0.6);
        box-shadow: 0 0 0 1px rgba(255, 140, 0, 0.2);
        background: rgba(255, 140, 0, 0.12);
        color: white;
    }
</style>
@endpush

@section('content')
    <div class="stats-grid">
        <div class="stat-card featured">
            <div class="stat-label">Plantillas</div>
            <div class="stat-value">{{ $stats['plantillas'] }}</div>
            <div class="stat-meta">Registradas en Meta</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Listados</div>
            <div class="stat-value">{{ $stats['listados'] }}</div>
            <div class="stat-meta">Grupos disponibles</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Personas</div>
            <div class="stat-value">{{ $stats['empleados'] }}</div>
            <div class="stat-meta">Contactos cargados</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Mensajes</div>
            <div class="stat-value">{{ $stats['mensajes'] }}</div>
            <div class="stat-meta">Registros enviados</div>
        </div>
    </div>

    <div class="tabbar">
        <a class="tab {{ $viewMode === 'calendar' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['monthValue']]) }}">Calendario</a>
        <a class="tab {{ $viewMode === 'history' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'history']) }}">Historial reciente</a>
    </div>

    @if ($viewMode === 'history')
        <section class="calendar-card">
            <div style="padding: 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: white;">Historial reciente</h2>
                <a class="button" href="{{ route('history.index') }}" style="background: rgba(255, 140, 0, 0.12); border: 1px solid rgba(255, 140, 0, 0.3); color: white;">Ver historial completo</a>
            </div>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead style="background: rgba(255, 255, 255, 0.02);">
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
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
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
        <section class="calendar-card">
            <div class="calendar-header">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <h4>Calendario de Envíos</h4>
                    <span class="calendar-month-badge">{{ $calendar['monthLabel'] }}</span>
                </div>
                <div class="calendar-nav">
                    <a href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['prevMonth']]) }}">← Anterior</a>
                    <a href="{{ route('dashboard', ['view' => 'calendar']) }}">Hoy</a>
                    <a href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['nextMonth']]) }}">Siguiente →</a>
                </div>
            </div>
            <div class="calendar-body">
                <div class="calendar-grid">
                    @foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dayName)
                        <div class="calendar-weekday">{{ $dayName }}</div>
                    @endforeach
                </div>
                <div class="calendar-grid">
                    @foreach ($calendar['weeks'] as $week)
                        @foreach ($week as $day)
                            <a
                                class="calendar-day {{ $day['inMonth'] ? '' : 'dim' }} {{ $selectedDate === $day['date'] ? 'active' : '' }} {{ $day['count'] > 0 ? 'has-events' : '' }}"
                                href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['monthValue'], 'date' => $day['date']]) }}"
                            >
                                <div class="calendar-day-number">{{ $day['label'] }}</div>
                                <div class="calendar-day-count">{{ $day['count'] }} envíos</div>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>

        @if ($selectedDate)
            <section class="calendar-card" style="margin-top: 24px;">
                <div style="padding: 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: white;">Detalle del {{ $selectedDate }}</h2>
                    <a class="button button-secondary" href="{{ route('dashboard', ['view' => 'calendar', 'month' => $calendar['monthValue']]) }}">Cerrar detalle</a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead style="background: rgba(255, 255, 255, 0.02);">
                            <tr>
                                <th>Hora</th>
                                <th>Persona</th>
                                <th>Teléfono</th>
                                <th>Listado</th>
                                <th>Plantilla</th>
                                <th>Estado</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dayLogs as $log)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
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
                                    <td colspan="7">No hay envíos en este día.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif
@endsection
