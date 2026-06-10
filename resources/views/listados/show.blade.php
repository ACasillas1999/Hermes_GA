@extends('layouts.app')

@section('title', $listado->nombre)
@section('subtitle', 'Gestiona las personas de este listado')

@section('header-actions')
    <div class="row-inline">
        <button class="button" type="button" data-open-modal="modal-import">Importar CSV</button>
        <button class="button" type="button" data-open-modal="modal-persona">Agregar persona</button>
        <a class="button button-secondary" href="{{ route('listados.index') }}">Volver</a>
    </div>
@endsection

@push('styles')
<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(5, 9, 18, 0.75);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 80;
        padding: 24px 16px;
        overflow-y: auto;
    }

    .modal-overlay.hidden {
        display: none;
    }

    .modal-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 24px;
        width: min(520px, 94vw);
        max-height: calc(100vh - 48px);
        overflow: auto;
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.4);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    body.modal-open {
        overflow: hidden;
    }
</style>
@endpush

@section('content')
    <div class="card">
        <h2>Personas</h2>
            <form method="GET" action="{{ route('listados.show', $listado) }}">
                <div class="row">
                    <label for="q">Buscar persona</label>
                    <input id="q" type="text" name="q" value="{{ $search }}" placeholder="Nombre o numero">
                </div>
                <div class="row-inline">
                    <button class="button" type="submit">Buscar</button>
                    <a class="button button-secondary" href="{{ route('listados.show', $listado) }}">Limpiar</a>
                </div>
            </form>

        <div style="margin-top: 16px; overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Numero</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empleados as $empleado)
                        <tr>
                            <td>
                                <input type="text" name="nombre" value="{{ $empleado->Nombre }}" form="update-{{ $empleado->ID }}" required>
                            </td>
                            <td>
                                <input type="text" name="numero" value="{{ $empleado->Numero }}" form="update-{{ $empleado->ID }}" required>
                            </td>
                            <td>
                                <input type="email" name="correo" value="{{ $empleado->Correo }}" form="update-{{ $empleado->ID }}">
                            </td>
                            <td>
                                <div class="row-inline">
                                    <form id="update-{{ $empleado->ID }}" method="POST" action="{{ route('listados.empleados.update', [$listado, $empleado]) }}">
                                        @csrf
                                        @method('PUT')
                                        <button class="button button-secondary" type="submit">Guardar</button>
                                    </form>
                                    <form method="POST" action="{{ route('listados.empleados.destroy', [$listado, $empleado]) }}" onsubmit="return confirm('Eliminar persona?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button button-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No hay personas en este listado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 12px;">
            {{ $empleados->links('components.pagination') }}
        </div>
    </div>

    <div class="modal-overlay hidden" id="modal-persona" role="dialog" aria-modal="true">
        <div class="modal-card">
            <div class="modal-header">
                <h2 style="margin: 0;">Agregar persona</h2>
                <button class="button button-secondary" type="button" data-close-modal>Cerrar</button>
            </div>
            <form method="POST" action="{{ route('listados.empleados.store', $listado) }}">
                @csrf
                <div class="row">
                    <label for="modal-nombre">Nombre</label>
                    <input id="modal-nombre" type="text" name="nombre" value="{{ old('nombre') }}" required>
                </div>
                <div class="row">
                    <label for="modal-numero">Numero</label>
                    <input id="modal-numero" type="text" name="numero" value="{{ old('numero') }}" required>
                </div>
                <div class="row">
                    <label for="modal-correo">Correo (opcional)</label>
                    <input id="modal-correo" type="email" name="correo" value="{{ old('correo') }}">
                </div>
                <div class="row-inline">
                    <button class="button" type="submit">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay hidden" id="modal-import" role="dialog" aria-modal="true">
        <div class="modal-card">
            <div class="modal-header">
                <h2 style="margin: 0;">Importar CSV</h2>
                <button class="button button-secondary" type="button" data-close-modal="modal-import">Cerrar</button>
            </div>
            <form method="POST" action="{{ route('listados.empleados.import', $listado) }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <p class="muted" style="margin-top: 0;">El archivo CSV puede tener las columnas Nombre, Numero, Correo.</p>
                    <label for="csv_file">Archivo CSV</label>
                    <input id="csv_file" type="file" name="csv_file" accept=".csv" required style="padding: 10px; border: 1px dashed var(--line); border-radius: 8px; cursor: pointer;">
                </div>
                <div class="row-inline" style="margin-top: 16px;">
                    <button class="button" type="submit">Subir e Importar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const modals = {
        'modal-persona': document.getElementById('modal-persona'),
        'modal-import': document.getElementById('modal-import')
    };

    function openModal(id) {
        const m = modals[id];
        if (!m) return;
        m.classList.remove('hidden');
        document.body.classList.add('modal-open');
        if (id === 'modal-persona') {
            setTimeout(() => document.getElementById('modal-nombre')?.focus(), 0);
        }
    }

    function closeModal(id) {
        const m = modals[id];
        if (!m) return;
        m.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    document.querySelectorAll('[data-open-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal(btn.dataset.openModal);
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(btn.dataset.closeModal || btn.closest('.modal-overlay').id);
        });
    });

    Object.values(modals).forEach(m => {
        if (!m) return;
        m.addEventListener('click', (event) => {
            if (event.target === m) {
                closeModal(m.id);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            Object.values(modals).forEach(m => {
                if (m && !m.classList.contains('hidden')) {
                    closeModal(m.id);
                }
            });
        }
    });
</script>
@endpush
