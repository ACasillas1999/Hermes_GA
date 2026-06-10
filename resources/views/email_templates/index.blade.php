@extends('layouts.app')

@section('title', 'Plantillas de Correo')
@section('subtitle', 'Gestiona las plantillas para correos masivos')

@section('header-actions')
    <form method="POST" action="{{ route('email_templates.sync') }}" style="display: inline-block;">
        @csrf
        <button class="button" type="submit">Sincronizar de Resend</button>
    </form>
    <button class="button button-secondary" type="button" data-open-modal="modal-template">Nueva plantilla manual</button>
@endsection

@push('styles')
<style>
    .template-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: 1fr;
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
        font-size: 14px;
    }

    .template-meta {
        font-size: 12px;
        color: var(--muted);
        margin-top: 4px;
    }

    .preview-box {
        border: 1px dashed var(--line);
        border-radius: 12px;
        padding: 14px;
        background: rgba(15, 23, 42, 0.7);
        min-height: 220px;
        display: grid;
        gap: 10px;
        align-content: start;
    }

    html[data-theme="light"] .preview-box {
        background: var(--card);
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(5, 9, 18, 0.75);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 40px 16px;
        z-index: 80;
        overflow-y: auto;
    }

    .modal-overlay.hidden {
        display: none;
    }

    .modal-card {
        width: min(700px, 95vw);
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.4);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
    <div class="template-grid">
        <section class="card">
            <h2>Plantillas guardadas</h2>
            <div class="template-list" id="template-list">
                @forelse ($templates as $template)
                    <div class="template-item" data-id="{{ $template->id }}" data-subject="{{ $template->subject }}" data-html="{{ $template->html_body }}">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <div class="template-title">{{ $template->name }}</div>
                                <div class="template-meta">Asunto: {{ $template->subject }}</div>
                            </div>
                            <form method="POST" action="{{ route('email_templates.destroy', $template) }}" onsubmit="return confirm('Eliminar plantilla?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button button-danger" style="padding: 4px 8px; font-size: 11px;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="muted">No hay plantillas de correo registradas.</div>
                @endforelse
            </div>
        </section>

        <section class="card">
            <h2>Previsualización</h2>
            <div class="preview-box" id="template-preview">
                <div class="muted">Selecciona una plantilla para verla aquí.</div>
            </div>
        </section>
    </div>

    <div id="modal-template" class="modal-overlay hidden" role="dialog" aria-modal="true">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h2 style="margin: 0;">Crear plantilla de correo</h2>
                </div>
                <button class="button button-secondary" type="button" data-close-modal="modal-template">Cerrar</button>
            </div>

            <form method="POST" action="{{ route('email_templates.store') }}">
                @csrf
                <div class="row">
                    <label for="name">Nombre interno (sin espacios)</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="promocion_febrero" required>
                </div>
                
                <div class="row">
                    <label for="subject">Asunto del correo</label>
                    <input id="subject" type="text" name="subject" value="{{ old('subject') }}" placeholder="¡Gran descuento en tu próxima compra!" required>
                </div>

                <div class="row">
                    <label for="html_body">Cuerpo (HTML)</label>
                    <textarea id="html_body" name="html_body" rows="12" style="font-family: monospace; font-size: 13px;" required placeholder="<h1>Hola!</h1><p>Tu contenido aquí...</p>">{{ old('html_body') }}</textarea>
                    <div class="muted" style="margin-top: 4px;">Usa HTML directo. Puedes incluir estilos inline.</div>
                </div>

                <div class="row-inline">
                    <button class="button" type="submit">Guardar plantilla</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const listEl = document.getElementById('template-list');
    const previewEl = document.getElementById('template-preview');
    const modal = document.getElementById('modal-template');
    
    function setActive(id) {
        document.querySelectorAll('.template-item').forEach((item) => {
            item.classList.toggle('active', item.dataset.id === String(id));
        });
    }

    listEl.addEventListener('click', (event) => {
        const item = event.target.closest('.template-item');
        if (!item) return;
        
        // Prevent clicking delete button from triggering preview
        if (event.target.tagName.toLowerCase() === 'button' || event.target.closest('form')) return;

        setActive(item.dataset.id);
        const subject = item.dataset.subject;
        const html = item.dataset.html;

        previewEl.innerHTML = '';
        const headerNode = document.createElement('div');
        headerNode.style.fontWeight = 'bold';
        headerNode.style.marginBottom = '12px';
        headerNode.style.paddingBottom = '8px';
        headerNode.style.borderBottom = '1px solid var(--line)';
        headerNode.textContent = `Asunto: ${subject}`;
        
        const iframeNode = document.createElement('iframe');
        iframeNode.style.width = '100%';
        iframeNode.style.height = '450px';
        iframeNode.style.border = 'none';
        iframeNode.style.borderRadius = '8px';
        iframeNode.style.backgroundColor = '#ffffff';
        iframeNode.srcdoc = html;

        previewEl.appendChild(headerNode);
        previewEl.appendChild(iframeNode);
    });

    document.querySelectorAll('[data-open-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const m = document.getElementById(btn.dataset.openModal);
            if (m) {
                m.classList.remove('hidden');
                document.body.classList.add('modal-open');
            }
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const m = document.getElementById(btn.dataset.closeModal);
            if (m) {
                m.classList.add('hidden');
                document.body.classList.remove('modal-open');
            }
        });
    });
</script>
@endpush
