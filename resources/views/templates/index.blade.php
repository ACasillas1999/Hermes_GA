@extends('layouts.app')

@section('title', 'Plantillas')
@section('subtitle', 'Previsualiza y crea nuevas plantillas de WhatsApp')

@section('header-actions')
    <button class="button" type="button" id="open-template-modal">Nueva plantilla</button>
@endsection

@push('styles')
<style>
    .template-grid {
        display: grid;
        gap: 14px;
    }

    @media (min-width: 980px) {
        .template-grid {
            grid-template-columns: 1.2fr 1fr;
        }
    }

    .template-list {
        display: grid;
        gap: 8px;
        max-height: 520px;
        overflow: auto;
    }

    .template-item {
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 10px 12px;
        cursor: pointer;
        background: rgba(15, 23, 42, 0.65);
    }

    html[data-theme="light"] .template-item {
        background: var(--card);
    }

    .template-item.active {
        border-color: rgba(56, 189, 248, 0.6);
        box-shadow: 0 10px 22px rgba(56, 189, 248, 0.12);
    }

    .template-title {
        font-weight: 600;
        font-size: 13px;
    }

    .template-meta {
        font-size: 11px;
        color: var(--muted);
        margin-top: 4px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border: 1px solid transparent;
    }

    .status-approved {
        color: #052e16;
        background: rgba(34, 197, 94, 0.2);
        border-color: rgba(34, 197, 94, 0.45);
    }

    .status-pending,
    .status-in_review {
        color: #3b1d00;
        background: rgba(251, 191, 36, 0.22);
        border-color: rgba(251, 191, 36, 0.45);
    }

    .status-rejected,
    .status-disabled {
        color: #3f0d0d;
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.45);
    }

    .status-paused {
        color: #0f172a;
        background: rgba(148, 163, 184, 0.25);
        border-color: rgba(148, 163, 184, 0.45);
    }

    html[data-theme="dark"] .status-approved {
        color: #bbf7d0;
    }

    html[data-theme="dark"] .status-pending,
    html[data-theme="dark"] .status-in_review {
        color: #fde68a;
    }

    html[data-theme="dark"] .status-rejected,
    html[data-theme="dark"] .status-disabled {
        color: #fecaca;
    }

    html[data-theme="dark"] .status-paused {
        color: #e2e8f0;
    }

    .preview-box {
        border: 1px dashed var(--line);
        border-radius: 12px;
        padding: 14px;
        background: rgba(15, 23, 42, 0.7);
        min-height: 220px;
        display: grid;
        gap: 10px;
    }

    html[data-theme="light"] .preview-box {
        background: var(--card);
    }

    .preview-header {
        font-weight: 600;
        margin-bottom: 6px;
        white-space: pre-wrap;
    }

    .preview-body {
        white-space: pre-wrap;
        line-height: 1.45;
    }

    .preview-footer {
        margin-top: 12px;
        color: var(--muted);
        font-size: 12px;
        white-space: pre-wrap;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(5, 9, 18, 0.7);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 40px 16px;
        z-index: 80;
    }

    .modal-overlay.hidden {
        display: none;
    }

    .modal-card {
        width: min(980px, 95vw);
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.4);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .modal-grid {
        display: grid;
        gap: 14px;
    }

    @media (min-width: 980px) {
        .modal-grid {
            grid-template-columns: 1.2fr 1fr;
        }
    }
</style>
@endpush

@section('content')
    <div class="template-grid">
        <section class="card">
            <div class="row-inline" style="justify-content: space-between;">
                <h2>Plantillas registradas</h2>
                <form method="POST" action="{{ route('templates.sync') }}">
                    @csrf
                    <button class="button button-secondary" type="submit">Sincronizar</button>
                </form>
            </div>

            <div class="row" style="margin-top: 10px;">
                <label for="template-search">Buscar</label>
                <input id="template-search" type="text" placeholder="Nombre o idioma">
            </div>

            <div class="template-list" id="template-list">
                @foreach ($templates as $template)
                    @php
                        $statusValue = strtolower((string) ($template->status ?? 'n/a'));
                        $statusClass = 'status-'.preg_replace('/[^a-z0-9_]/', '_', $statusValue);
                    @endphp
                    <div class="template-item" data-id="{{ $template->id }}">
                        <div class="template-title">{{ $template->name }}</div>
                        <div class="template-meta">
                            {{ $template->language }}
                            · <span class="status-badge {{ $statusClass }}">{{ $template->status ?? 'N/A' }}</span>
                            · {{ $template->category ?? 'N/A' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2>Previsualizacion</h2>
            <div class="preview-box" id="template-preview">
                <div class="muted">Selecciona una plantilla para verla aqui.</div>
            </div>
        </section>
    </div>
    <div id="template-modal" class="modal-overlay hidden" role="dialog" aria-modal="true">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h2 style="margin: 0;">Crear plantilla nueva</h2>
                    <div class="muted">La vista previa se actualiza mientras escribes.</div>
                </div>
                <button class="button button-secondary" type="button" id="close-template-modal">Cerrar</button>
            </div>

            <div class="modal-grid">
                <form id="create-template-form" method="POST" action="{{ route('templates.store') }}">
                    @csrf
                    <div class="row-inline">
                        <div style="flex: 1;">
                            <label for="name">Nombre (sin espacios)</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="mi_plantilla_2026" required>
                            <div class="muted">Solo minusculas, numeros y guion bajo. Se normaliza automaticamente.</div>
                        </div>
                        <div>
                            <label for="language">Idioma</label>
                            <input id="language" type="text" name="language" value="{{ old('language', 'es_MX') }}" required>
                        </div>
                        <div>
                            <label for="category">Categoria</label>
                            <select id="category" name="category" required>
                                <option value="MARKETING" @selected(old('category') === 'MARKETING')>MARKETING</option>
                                <option value="UTILITY" @selected(old('category') === 'UTILITY')>UTILITY</option>
                                <option value="AUTHENTICATION" @selected(old('category') === 'AUTHENTICATION')>AUTHENTICATION</option>
                            </select>
                        </div>
                    </div>

                    <div class="row-inline">
                        <div style="flex: 1;">
                            <label for="header_type">Encabezado</label>
                            <select id="header_type" name="header_type">
                                <option value="NONE" @selected(old('header_type') === 'NONE')>Sin encabezado</option>
                                <option value="TEXT" @selected(old('header_type') === 'TEXT')>Texto</option>
                                <option value="IMAGE" @selected(old('header_type') === 'IMAGE')>Imagen</option>
                                <option value="VIDEO" @selected(old('header_type') === 'VIDEO')>Video</option>
                                <option value="DOCUMENT" @selected(old('header_type') === 'DOCUMENT')>Documento</option>
                            </select>
                        </div>
                        <div style="flex: 2;" id="header-text-wrap">
                            <label for="header_text">Texto de encabezado</label>
                            <input id="header_text" type="text" name="header_text" value="{{ old('header_text') }}" placeholder="Titulo corto">
                        </div>
                        <div style="flex: 2;" id="header-media-wrap">
                            <label for="header_media_url">URL ejemplo (solo multimedia)</label>
                            <input id="header_media_url" type="text" name="header_media_url" value="{{ old('header_media_url') }}" placeholder="https://...">
                        </div>
                    </div>

                    <div class="row">
                        <label for="body_text">Cuerpo</label>
                        <textarea id="body_text" name="body_text" rows="4" required>{{ old('body_text') }}</textarea>
                        @verbatim
                        <div class="muted">Usa variables con {{1}}, {{2}}. Se crean ejemplos automaticamente.</div>
                        @endverbatim
                    </div>

                    <div class="row">
                        <label for="footer_text">Footer (opcional)</label>
                        <input id="footer_text" type="text" name="footer_text" value="{{ old('footer_text') }}" placeholder="Texto pequeno">
                    </div>

                    <div class="row-inline">
                        <button class="button" type="submit">Crear plantilla</button>
                    </div>
                </form>

                <div>
                    <h3 style="margin-top: 0;">Vista previa en vivo</h3>
                    <div class="preview-box" id="live-preview">
                        <div class="muted">Completa el formulario para ver la vista previa.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const templates = @json($templatesForJs);
    const listEl = document.getElementById('template-list');
    const previewEl = document.getElementById('template-preview');
    const searchEl = document.getElementById('template-search');
    const modal = document.getElementById('template-modal');
    const openModalBtn = document.getElementById('open-template-modal');
    const closeModalBtn = document.getElementById('close-template-modal');
    const formEl = document.getElementById('create-template-form');
    const livePreviewEl = document.getElementById('live-preview');
    const headerTypeEl = document.getElementById('header_type');
    const headerTextWrap = document.getElementById('header-text-wrap');
    const headerMediaWrap = document.getElementById('header-media-wrap');
    const nameInput = document.getElementById('name');

    function renderPreview(template) {
        if (!template) {
            previewEl.innerHTML = '<div class="muted">Selecciona una plantilla para verla aqui.</div>';
            return;
        }

        const headerFormat = (template.headerFormat || '').toUpperCase();
        const headerText = template.headerText || '';
        const bodyText = template.bodyText || 'Sin cuerpo';
        const footer = (template.components || []).find((item) => (item.type || '').toUpperCase() === 'FOOTER');
        const footerText = footer?.text || '';

        previewEl.innerHTML = '';
        const headerNode = document.createElement('div');
        if (!headerFormat) {
            headerNode.className = 'muted';
            headerNode.textContent = 'Sin encabezado.';
        } else if (headerFormat === 'TEXT') {
            headerNode.className = 'preview-header';
            headerNode.textContent = headerText || 'Encabezado de texto';
        } else {
            headerNode.className = 'preview-header';
            headerNode.textContent = `Encabezado ${headerFormat.toLowerCase()} (media)`;
        }

        const bodyNode = document.createElement('div');
        bodyNode.className = 'preview-body';
        bodyNode.textContent = bodyText;

        previewEl.appendChild(headerNode);
        previewEl.appendChild(bodyNode);

        if (footerText) {
            const footerNode = document.createElement('div');
            footerNode.className = 'preview-footer';
            footerNode.textContent = footerText;
            previewEl.appendChild(footerNode);
        }
    }

    function setActive(id) {
        document.querySelectorAll('.template-item').forEach((item) => {
            item.classList.toggle('active', item.dataset.id === String(id));
        });
    }

    listEl.addEventListener('click', (event) => {
        const item = event.target.closest('.template-item');
        if (!item) return;
        const template = templates.find((t) => String(t.id) === String(item.dataset.id));
        setActive(item.dataset.id);
        renderPreview(template);
    });

    searchEl.addEventListener('input', () => {
        const value = searchEl.value.toLowerCase();
        document.querySelectorAll('.template-item').forEach((item) => {
            const template = templates.find((t) => String(t.id) === String(item.dataset.id));
            const haystack = `${template?.name || ''} ${template?.language || ''}`.toLowerCase();
            item.style.display = haystack.includes(value) ? '' : 'none';
        });
    });

    function toggleHeaderFields() {
        const type = (headerTypeEl.value || 'NONE').toUpperCase();
        headerTextWrap.style.display = type === 'TEXT' ? '' : 'none';
        headerMediaWrap.style.display = ['IMAGE', 'VIDEO', 'DOCUMENT'].includes(type) ? '' : 'none';
    }

    function renderLivePreview() {
        const headerType = (headerTypeEl.value || 'NONE').toUpperCase();
        const headerText = document.getElementById('header_text').value || '';
        const bodyText = document.getElementById('body_text').value || '';
        const footerText = document.getElementById('footer_text').value || '';

        livePreviewEl.innerHTML = '';
        const headerNode = document.createElement('div');
        if (headerType === 'NONE') {
            headerNode.className = 'muted';
            headerNode.textContent = 'Sin encabezado.';
        } else if (headerType === 'TEXT') {
            headerNode.className = 'preview-header';
            headerNode.textContent = headerText || 'Encabezado de texto';
        } else {
            headerNode.className = 'preview-header';
            headerNode.textContent = `Encabezado ${headerType.toLowerCase()} (media)`;
        }

        const bodyNode = document.createElement('div');
        bodyNode.className = 'preview-body';
        bodyNode.textContent = bodyText || 'Escribe el cuerpo del mensaje...';

        livePreviewEl.appendChild(headerNode);
        livePreviewEl.appendChild(bodyNode);

        if (footerText) {
            const footerNode = document.createElement('div');
            footerNode.className = 'preview-footer';
            footerNode.textContent = footerText;
            livePreviewEl.appendChild(footerNode);
        }
    }

    function openModal() {
        modal.classList.remove('hidden');
        toggleHeaderFields();
        renderLivePreview();
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    openModalBtn?.addEventListener('click', openModal);
    closeModalBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    headerTypeEl?.addEventListener('change', () => {
        toggleHeaderFields();
        renderLivePreview();
    });

    formEl?.addEventListener('input', renderLivePreview);
    nameInput?.addEventListener('blur', () => {
        const raw = nameInput.value || '';
        const normalized = raw
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9_]/g, '');
        nameInput.value = normalized;
    });

    toggleHeaderFields();
</script>
@endpush
