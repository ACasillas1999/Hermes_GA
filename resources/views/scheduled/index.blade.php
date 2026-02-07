@extends('layouts.app')

@section('title', 'Programar envios')
@section('subtitle', 'Agenda envios masivos en horario de Mexico')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        margin-bottom: 14px;
    }

    .stat-card {
        display: grid;
        gap: 6px;
        position: relative;
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
        width: 80px;
        height: 80px;
        background: radial-gradient(circle, rgba(255, 140, 0, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-label {
        color: var(--muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        position: relative;
        z-index: 1;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }

    .mini-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .mini-table th,
    .mini-table td {
        padding: 6px 8px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        text-align: left;
    }

    .mini-table th {
        color: var(--muted);
        font-weight: 600;
    }

    .pill {
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        border: 1px solid rgba(255, 140, 0, 0.35);
        background: rgba(255, 140, 0, 0.12);
    }

    .error-text {
        color: #fecaca;
        font-size: 11px;
        line-height: 1.2;
    }

    .template-preview {
        background: rgba(15, 23, 42, 0.8);
        border: 1px dashed var(--line);
        border-radius: 12px;
        padding: 14px;
        font-size: 14px;
        color: var(--muted);
    }

    .param-list {
        display: grid;
        gap: 10px;
    }

    .hint {
        font-size: 11px;
        color: var(--muted);
    }

    /* Calendar styles */
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

    .calendar-nav a {
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        text-decoration: none;
        transition: all 0.2s;
    }

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
</style>
@endpush

@section('content')
    <div class="stats-grid">
        <div class="card stat-card featured">
            <div class="stat-label">Pendientes</div>
            <div class="stat-value">{{ $stats['pendientes'] }}</div>
            <div class="muted">En espera de envio</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Enviados</div>
            <div class="stat-value">{{ $stats['enviados'] }}</div>
            <div class="muted">Completados</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Fallidos</div>
            <div class="stat-value">{{ $stats['fallidos'] }}</div>
            <div class="muted">Con errores</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Zona horaria</div>
            <div class="stat-value">{{ $timezone }}</div>
            <div class="muted">Mexico</div>
        </div>
    </div>

    <div class="tabbar">
        <a class="tab {{ $viewMode === 'list' ? 'active' : '' }}" href="{{ route('scheduled.index', ['view' => 'list']) }}">Lista</a>
        <a class="tab {{ $viewMode === 'calendar' ? 'active' : '' }}" href="{{ route('scheduled.index', ['view' => 'calendar', 'month' => $calendar['monthValue']]) }}">Calendario</a>
    </div>

    @if ($viewMode === 'calendar')
        <section class="calendar-card">
            <div class="calendar-header">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <h4>Mensajes Programados</h4>
                    <span class="calendar-month-badge">{{ $calendar['monthLabel'] }}</span>
                </div>
                <div class="calendar-nav">
                    <a href="{{ route('scheduled.index', ['view' => 'calendar', 'month' => $calendar['prevMonth']]) }}">← Anterior</a>
                    <a href="{{ route('scheduled.index', ['view' => 'calendar']) }}">Hoy</a>
                    <a href="{{ route('scheduled.index', ['view' => 'calendar', 'month' => $calendar['nextMonth']]) }}">Siguiente →</a>
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
                                href="{{ route('scheduled.index', ['view' => 'calendar', 'month' => $calendar['monthValue'], 'date' => $day['date']]) }}"
                            >
                                <div class="calendar-day-number">{{ $day['label'] }}</div>
                                <div class="calendar-day-count">{{ $day['count'] }} programados</div>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>

        @if ($selectedDate && $dayScheduled->count() > 0)
            <section class="card" style="margin-top: 24px;">
                <div style="padding: 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: white;">Programados para {{ $selectedDate }}</h2>
                    <a class="button button-secondary" href="{{ route('scheduled.index', ['view' => 'calendar', 'month' => $calendar['monthValue']]) }}">Cerrar detalle</a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="mini-table">
                        <thead style="background: rgba(255, 255, 255, 0.02);">
                            <tr>
                                <th>Hora</th>
                                <th>Listado</th>
                                <th>Plantilla</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dayScheduled as $item)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td>{{ optional($item->scheduled_at)->format('H:i') }}</td>
                                    <td>{{ $item->listado?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $item->template?->name ?? 'N/A' }}</td>
                                    <td><span class="pill">{{ $item->status }}</span></td>
                                    <td>
                                        <div class="row-inline">
                                            <a class="button button-secondary" href="{{ route('scheduled.show', $item) }}">Detalle</a>
                                            @if (in_array($item->status, ['pending', 'queueing', 'queued'], true))
                                                <form method="POST" action="{{ route('scheduled.destroy', $item) }}" onsubmit="return confirm('Cancelar envio programado?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="button button-danger" type="submit">Cancelar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @else
        <div class="grid">
            <section class="card">
                <h2>Programar envio</h2>
                <form id="schedule-form" method="POST" action="{{ route('scheduled.store') }}">
                    @csrf
                    <div class="row">
                        <label for="listado_id">Listado</label>
                        <select id="listado_id" name="listado_id" required>
                            <option value="">Selecciona un listado</option>
                            @foreach ($listados as $listado)
                                <option value="{{ $listado->id }}" @selected(old('listado_id') == $listado->id)>
                                    {{ $listado->nombre }} ({{ $listado->empleados_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <label for="scheduled_template_id">Plantilla</label>
                        <select id="scheduled_template_id" name="template_id" required>
                            <option value="">Selecciona una plantilla</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>
                                    {{ $template->name }} ({{ $template->language }}) - {{ $template->status }}@if($template->headerFormat()) | Header: {{ strtoupper($template->headerFormat()) }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <label for="scheduled_at">Fecha y hora (Mexico)</label>
                        @php
                            $defaultSchedule = now()->addHour()->format('Y-m-d\\TH:i');
                        @endphp
                        <input id="scheduled_at" type="datetime-local" name="scheduled_at" required value="{{ old('scheduled_at', $defaultSchedule) }}">
                        <div class="hint">Se enviara usando la zona horaria {{ $timezone }}.</div>
                    </div>

                    <div class="row">
                        <label>Encabezado</label>
                        <div id="scheduled-header-preview" class="template-preview">Esta plantilla no requiere encabezado.</div>
                        <div id="scheduled-header-params" class="param-list"></div>
                    </div>

                    <div class="row">
                        <label>Preview</label>
                        <div id="scheduled-template-preview" class="template-preview">Selecciona una plantilla para ver el contenido.</div>
                    </div>

                    <div class="row">
                        <label>Parametros</label>
                        <div id="scheduled-template-params" class="param-list"></div>
                        <div class="muted">Si la plantilla no tiene variables, no se requiere nada.</div>
                    </div>

                    <div class="row-inline">
                        <button class="button" type="submit">Programar envio</button>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="row-inline" style="justify-content: space-between;">
                    <h2>Envios programados</h2>
                    <span class="badge">{{ $scheduledMessages->count() }} registros</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Programado</th>
                                <th>Listado</th>
                                <th>Plantilla</th>
                                <th>Estado</th>
                                <th>Enviado</th>
                                <th>Error</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($scheduledMessages as $item)
                                <tr>
                                    <td>{{ optional($item->scheduled_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $item->listado?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $item->template?->name ?? 'N/A' }}</td>
                                    <td><span class="pill">{{ $item->status }}</span></td>
                                    <td>{{ optional($item->sent_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td>
                                        @if ($item->error)
                                            <div class="error-text" title="{{ $item->error }}">{{ $item->error }}</div>
                                        @else
                                            <span class="muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="row-inline">
                                            <a class="button button-secondary" href="{{ route('scheduled.show', $item) }}">Detalle</a>
                                            @if (in_array($item->status, ['pending', 'queueing', 'queued'], true))
                                                <form method="POST" action="{{ route('scheduled.destroy', $item) }}" onsubmit="return confirm('Cancelar envio programado?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="button button-danger" type="submit">Cancelar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No hay envios programados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    const templates = @json($templatesForJs);

    const templateSelect = document.getElementById('scheduled_template_id');
    const previewEl = document.getElementById('scheduled-template-preview');
    const paramsEl = document.getElementById('scheduled-template-params');
    const headerPreviewEl = document.getElementById('scheduled-header-preview');
    const headerParamsEl = document.getElementById('scheduled-header-params');

    function updateTemplateUI() {
        const selectedId = templateSelect.value;
        const template = templates.find((item) => String(item.id) === String(selectedId));

        if (!template) {
            previewEl.textContent = 'Selecciona una plantilla para ver el contenido.';
            paramsEl.innerHTML = '';
            headerPreviewEl.textContent = 'Esta plantilla no requiere encabezado.';
            headerParamsEl.innerHTML = '';
            return;
        }

        previewEl.textContent = template.bodyText || 'Sin cuerpo definido.';
        paramsEl.innerHTML = '';
        headerParamsEl.innerHTML = '';

        const headerFormat = (template.headerFormat || '').toUpperCase();
        const headerCount = Number(template.headerCount || 0);
        const needsMedia = ['IMAGE', 'VIDEO', 'DOCUMENT'].includes(headerFormat);

        if (!headerFormat) {
            headerPreviewEl.textContent = 'Esta plantilla no requiere encabezado.';
        } else if (headerFormat === 'TEXT') {
            headerPreviewEl.textContent = template.headerText || 'Encabezado de texto.';
        } else {
            headerPreviewEl.textContent = `Encabezado con ${headerFormat.toLowerCase()}. Se requiere URL publica.`;
        }

        if (needsMedia) {
            const wrapper = document.createElement('div');
            const label = document.createElement('label');
            label.textContent = `URL de ${headerFormat.toLowerCase()}`;
            label.style.marginBottom = '4px';
            label.style.display = 'block';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'header_media_url';
            input.placeholder = 'https://...';
            input.required = true;

            const hint = document.createElement('div');
            hint.className = 'hint';
            hint.textContent = 'Debe ser una URL publica accesible por Meta.';

            wrapper.appendChild(label);
            wrapper.appendChild(input);
            wrapper.appendChild(hint);
            headerParamsEl.appendChild(wrapper);
        } else if (headerFormat === 'TEXT' && headerCount > 0) {
            for (let i = 0; i < headerCount; i += 1) {
                const wrapper = document.createElement('div');

                const label = document.createElement('label');
                label.textContent = `Parametro header ${i + 1}`;
                label.style.marginBottom = '4px';
                label.style.display = 'block';

                const input = document.createElement('input');
                input.type = 'text';
                input.name = `header_text_params[${i}]`;
                input.placeholder = `Valor ${i + 1}`;
                input.required = true;

                wrapper.appendChild(label);
                wrapper.appendChild(input);
                headerParamsEl.appendChild(wrapper);
            }
        }

        const count = Number(template.bodyCount || 0);

        for (let i = 0; i < count; i += 1) {
            const wrapper = document.createElement('div');

            const label = document.createElement('label');
            label.textContent = `Parametro ${i + 1}`;
            label.style.marginBottom = '4px';
            label.style.display = 'block';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = `body_params[${i}]`;
            input.placeholder = `Valor ${i + 1}`;
            input.required = true;

            wrapper.appendChild(label);
            wrapper.appendChild(input);
            paramsEl.appendChild(wrapper);
        }
    }

    templateSelect.addEventListener('change', updateTemplateUI);
    updateTemplateUI();

</script>
@endpush
