@extends('layouts.app')

@section('title', $listado->nombre)
@section('subtitle', 'Gestiona las personas de este listado')

@section('header-actions')
    <div class="row-inline">
        <button class="button" type="button" data-open-modal>Agregar persona</button>
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
    }

    .modal-overlay.hidden {
        display: none;
    }

    .modal-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 24px;
        width: min(520px, 92vw);
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.4);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
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
                            <td colspan="3">No hay personas en este listado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 12px;">
            {{ $empleados->links() }}
        </div>
    </div>

    <div class="modal-overlay hidden" id="modal-persona">
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
                <div class="row-inline">
                    <button class="button" type="submit">Agregar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('modal-persona');
    const openBtn = document.querySelector('[data-open-modal]');
    const closeBtn = document.querySelector('[data-close-modal]');

    function openModal() {
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
</script>
@endpush
