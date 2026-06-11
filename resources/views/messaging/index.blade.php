@extends('layouts.app')

@section('title', 'Envio masivo')
@section('subtitle', 'Envio masivo con plantillas sincronizadas desde Meta')

@section('header-actions')
    <form method="POST" action="{{ route('templates.sync') }}">
        @csrf
        <button class="button" type="submit">Sincronizar ahora</button>
    </form>
@endsection

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

    .progress-bar {
        width: 100%;
        height: 10px;
        background: #0b1220;
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid var(--line);
    }

    .progress-bar span {
        display: block;
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--accent), var(--accent-strong));
        transition: width 0.3s ease;
    }

    .progress-meta {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: var(--muted);
        margin-top: 8px;
    }

    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(5, 9, 18, 0.75);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
    }

    .overlay.hidden {
        display: none;
    }

    .overlay-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 24px;
        width: min(420px, 90vw);
        text-align: center;
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.4);
    }

    .spinner {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 3px solid rgba(255, 140, 0, 0.25);
        border-top-color: var(--accent);
        margin: 0 auto 16px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@section('content')
    <div class="stats-grid">
        <div class="card stat-card featured">
            <div class="stat-label">Plantillas</div>
            <div class="stat-value">{{ $stats['plantillas'] }}</div>
            <div class="muted">Registradas en Meta</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Listados</div>
            <div class="stat-value">{{ $stats['listados'] }}</div>
            <div class="muted">Grupos activos</div>
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

    <section id="progress-card" class="card" style="display: none;">
        <h2>Progreso de envio</h2>
        <div class="progress-bar">
            <span id="progress-fill"></span>
        </div>
        <div class="progress-meta">
            <span id="progress-text">0%</span>
            <span id="progress-detail">0 / 0</span>
        </div>
        <div id="progress-status" class="muted" style="margin-top: 8px;">Esperando...</div>
    </section>

    <div class="grid">
        <section class="card">
            <h2>Enviar mensaje</h2>
            <form id="send-form" method="POST" action="{{ route('messages.send') }}">
                @csrf
                <div class="row">
                    <label for="send_type">Tipo de envío</label>
                    <select id="send_type" name="send_type" required>
                        <option value="whatsapp" @selected(old('send_type') == 'whatsapp')>WhatsApp</option>
                        <option value="email" @selected(old('send_type') == 'email')>Correo Electrónico</option>
                    </select>
                </div>

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

                <div class="row" id="whatsapp_template_section">
                    <label for="template_id">Plantilla WABA</label>
                    <select id="template_id" name="template_id">
                        <option value="">Selecciona una plantilla de WhatsApp</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>
                                {{ $template->name }} ({{ $template->language }}) - {{ $template->status }}@if($template->headerFormat()) | Header: {{ strtoupper($template->headerFormat()) }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row" id="email_template_section" style="display: none;">
                    <label for="email_template_id">Plantilla de Correo</label>
                    <select id="email_template_id" name="email_template_id">
                        <option value="">Selecciona una plantilla de correo</option>
                        @foreach ($emailTemplates as $et)
                            <option value="{{ $et->id }}" @selected(old('email_template_id') == $et->id)>
                                {{ $et->name }} (Asunto: {{ $et->subject ?: 'Sin asunto' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row" id="email_subject_section" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label for="email_from_name">Nombre del remitente</label>
                            <input type="text" id="email_from_name" name="email_from_name" value="{{ old('email_from_name', env('MAIL_FROM_NAME', 'Hermes GA')) }}" placeholder="Tu Empresa">
                        </div>
                        <div>
                            <label for="email_from_address">Correo del remitente</label>
                            <input type="email" id="email_from_address" name="email_from_address" value="{{ old('email_from_address', env('MAIL_FROM_ADDRESS', 'onboarding@resend.dev')) }}" placeholder="ventas@tuempresa.com">
                        </div>
                    </div>
                    
                    <label for="email_subject">Asunto del correo (opcional si la plantilla ya lo tiene)</label>
                    <input type="text" id="email_subject" name="email_subject" value="{{ old('email_subject') }}" placeholder="Escribe el asunto del correo aquí...">
                </div>

                <div id="waba_params_section">
                    <div class="row">
                        <label>Encabezado</label>
                        <div id="header-preview" class="template-preview">Esta plantilla no requiere encabezado.</div>
                        <div id="header-params" class="param-list"></div>
                    </div>

                    <div class="row">
                        <label>Preview</label>
                        <div id="template-preview" class="template-preview">Selecciona una plantilla para ver el contenido.</div>
                    </div>

                    <div class="row">
                        <label>Parametros</label>
                        <div id="template-params" class="param-list"></div>
                        <div class="muted">Si la plantilla no tiene variables, no se requiere nada.</div>
                    </div>
                </div>

                <div id="email_preview_section" style="display: none;">
                    <div class="row">
                        <label>Preview de Correo</label>
                        <div id="email-preview-box" class="template-preview">Selecciona una plantilla de correo para verla aquí.</div>
                    </div>
                </div>

                <div class="row" id="email_params_section" style="display: none;">
                    <label>Variables de la Plantilla de Correo</label>
                    <div id="email-params" class="param-list"></div>
                    <div class="muted">Si la plantilla no tiene variables (como @{{{nombre}}}), no aparecerá nada aquí.</div>
                </div>

                <div class="row-inline">
                    <button class="button" type="submit">Enviar mensajes</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="row-inline" style="justify-content: space-between;">
                <h2>Historial reciente</h2>
                <a class="button button-secondary" href="{{ route('history.index') }}">Ver historial completo</a>
            </div>
            <div style="overflow-x: auto;">
                <table class="mini-table">
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
                                <td>{{ optional($log->sent_at)->format('m-d H:i') ?? $log->created_at->format('m-d H:i') }}</td>
                                <td>{{ $log->empleado?->Nombre ?? 'Sin nombre' }}</td>
                                <td>{{ $log->empleado?->listado?->nombre ?? 'N/A' }}</td>
                                <td>{{ $log->template_name }} @if($log->template_language) ({{ $log->template_language }}) @endif</td>
                                <td><span class="pill">{{ $log->status }}</span></td>
                                <td>
                                    @php
                                        $errorMessage = $log->error ?? data_get($log->response, 'error.message');
                                    @endphp
                                    @if ($log->status === 'failed' && $errorMessage)
                                        <div class="error-text" title="{{ $errorMessage }}">{{ $errorMessage }}</div>
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
    </div>

    <div id="progress-overlay" class="overlay hidden">
        <div class="overlay-card">
            <div class="spinner"></div>
            <div style="font-weight: 600;">Enviando mensajes...</div>
            <div class="muted" style="margin-top: 6px;">No cierres esta ventana.</div>
            <div style="margin-top: 16px;" class="progress-bar">
                <span id="overlay-progress-fill"></span>
            </div>
            <div class="progress-meta">
                <span id="overlay-progress-text">0%</span>
                <span id="overlay-progress-detail">0 / 0</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const templates = @json($templatesForJs);
    const emailTemplates = @json($emailTemplates);
    const emailTemplatesForJs = @json($emailTemplatesForJs);
    const batchId = @json($batchId);

    const templateSelect = document.getElementById('template_id');
    const previewEl = document.getElementById('template-preview');
    const paramsEl = document.getElementById('template-params');
    const headerPreviewEl = document.getElementById('header-preview');
    const headerParamsEl = document.getElementById('header-params');
    const sendForm = document.getElementById('send-form');
    const progressCard = document.getElementById('progress-card');
    const progressFill = document.getElementById('progress-fill');
    const progressText = document.getElementById('progress-text');
    const progressDetail = document.getElementById('progress-detail');
    const progressStatus = document.getElementById('progress-status');
    const overlay = document.getElementById('progress-overlay');
    const overlayFill = document.getElementById('overlay-progress-fill');
    const overlayText = document.getElementById('overlay-progress-text');
    const overlayDetail = document.getElementById('overlay-progress-detail');

    const sendTypeSelect = document.getElementById('send_type');
    const wabaSection = document.getElementById('whatsapp_template_section');
    const emailSection = document.getElementById('email_template_section');
    const emailSubjectSection = document.getElementById('email_subject_section');
    const wabaParams = document.getElementById('waba_params_section');
    const emailPreviewSection = document.getElementById('email_preview_section');
    const emailTemplateSelect = document.getElementById('email_template_id');
    const emailPreviewBox = document.getElementById('email-preview-box');

    const emailParamsSection = document.getElementById('email_params_section');
    const emailParamsEl = document.getElementById('email-params');

    function toggleSendType() {
        if (sendTypeSelect.value === 'email') {
            wabaSection.style.display = 'none';
            wabaParams.style.display = 'none';
            emailSection.style.display = 'block';
            emailSubjectSection.style.display = 'block';
            emailPreviewSection.style.display = 'block';
            emailParamsSection.style.display = 'block';
            templateSelect.required = false;
            emailTemplateSelect.required = true;
        } else {
            wabaSection.style.display = 'block';
            wabaParams.style.display = 'block';
            emailSection.style.display = 'none';
            emailSubjectSection.style.display = 'none';
            emailPreviewSection.style.display = 'none';
            emailParamsSection.style.display = 'none';
            templateSelect.required = true;
            emailTemplateSelect.required = false;
        }
    }

    sendTypeSelect.addEventListener('change', toggleSendType);
    toggleSendType();

    function updateEmailPreview() {
        const selectedId = emailTemplateSelect.value;
        const template = emailTemplatesForJs.find(t => String(t.id) === String(selectedId));
        if (!template) {
            emailPreviewBox.innerHTML = 'Selecciona una plantilla de correo para verla aquí.';
            emailParamsEl.innerHTML = '';
            return;
        }
        emailPreviewBox.innerHTML = `<strong>Asunto:</strong> ${template.subject}<hr style="border:0; border-bottom: 1px solid var(--line); margin: 8px 0;" />` + template.html_body;
        
        emailParamsEl.innerHTML = '';
        if (template.variables && template.variables.length > 0) {
            // These are automatically filled from the contact list
            const autoVars = ['nombre', 'Nombre', 'name', 'correo', 'Correo', 'email', 'numero', 'Numero', 'phone', 'puesto', 'Puesto'];
            
            let hasManualVars = false;
            
            template.variables.forEach(variable => {
                if (autoVars.includes(variable)) {
                    // Show info badge instead of input
                    const badge = document.createElement('div');
                    badge.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,140,0,0.08);border:1px solid rgba(255,140,0,0.25);border-radius:8px;font-size:13px;';
                    badge.innerHTML = '<span style="color:var(--accent);">✓</span> <strong style="color:var(--accent);">{{' + variable + '}}</strong> <span style="color:var(--muted);">— Se rellena automáticamente con el nombre del contacto del listado</span>';
                    emailParamsEl.appendChild(badge);
                } else {
                    hasManualVars = true;
                    const wrapper = document.createElement('div');
                    const label = document.createElement('label');
                    label.textContent = 'Valor para {{' + variable + '}}';
                    label.style.marginBottom = '4px';
                    label.style.display = 'block';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = `email_params[${variable}]`;
                    input.placeholder = `Escribe el valor para ${variable}`;
                    input.required = true;

                    wrapper.appendChild(label);
                    wrapper.appendChild(input);
                    emailParamsEl.appendChild(wrapper);
                }
            });
        }
    }

    emailTemplateSelect.addEventListener('change', updateEmailPreview);
    updateEmailPreview();

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
            headerPreviewEl.textContent = `Encabezado con ${headerFormat.toLowerCase()}. Se requiere URL pública.`;
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
            hint.textContent = 'Debe ser una URL pública accesible por Meta.';

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

    function showProgressUI() {
        progressCard.style.display = 'block';
        overlay.classList.remove('hidden');
    }

    function hideOverlay() {
        overlay.classList.add('hidden');
    }

    function updateProgress(data) {
        const percent = Number(data.progress || 0);
        const processed = Number(data.processed || 0);
        const total = Number(data.total || 0);
        const pending = Number(data.pending ?? (total - processed));
        const failed = Number(data.failed || 0);

        progressFill.style.width = `${percent}%`;
        overlayFill.style.width = `${percent}%`;
        progressText.textContent = `${percent}%`;
        overlayText.textContent = `${percent}%`;
        progressDetail.textContent = `${processed} / ${total}`;
        overlayDetail.textContent = `${processed} / ${total}`;
        progressStatus.textContent = `Fallidos: ${failed}`;

        if (data.finished || pending === 0 || processed >= total) {
            hideOverlay();
            progressStatus.textContent = `Finalizado. Fallidos: ${failed}`;
        }
    }

    async function pollBatch() {
        if (!batchId) {
            return;
        }

        showProgressUI();

        const poll = async () => {
            try {
                const response = await fetch(`/batches/${batchId}`);
                if (!response.ok) {
                    progressStatus.textContent = 'No se pudo obtener el progreso.';
                    hideOverlay();
                    return;
                }
                const data = await response.json();
                updateProgress(data);

                if (!data.finished) {
                    setTimeout(poll, 1500);
                }
            } catch (error) {
                setTimeout(poll, 2000);
            }
        };

        poll();
    }

    sendForm.addEventListener('submit', () => {
        showProgressUI();
    });

    pollBatch();
</script>
@endpush
