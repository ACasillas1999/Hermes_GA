@extends('layouts.app')

@section('title', 'Listados')
@section('subtitle', 'Gestiona tus listados y personas')

@section('header-actions')
    <a class="button" href="{{ route('listados.create') }}">Nuevo listado</a>
@endsection

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('listados.index') }}">
            <div class="row">
                <label for="q">Buscar listado</label>
                <input id="q" type="text" name="q" value="{{ $search }}" placeholder="Nombre del listado">
            </div>
            <div class="row-inline">
                <button class="button" type="submit">Buscar</button>
                <a class="button button-secondary" href="{{ route('listados.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top: 20px;">
        <table class="table">
            <thead>
                <tr>
                    <th>Listado</th>
                    <th>Personas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listados as $listado)
                    <tr>
                        <td>{{ $listado->nombre }}</td>
                        <td><span class="badge">{{ $listado->empleados_count }} personas</span></td>
                        <td>
                            <div class="row-inline">
                                <a class="button button-secondary" href="{{ route('listados.show', $listado) }}">Ver</a>
                                <a class="button button-secondary" href="{{ route('listados.edit', $listado) }}">Editar</a>
                                <form method="POST" action="{{ route('listados.destroy', $listado) }}" onsubmit="return confirm('Eliminar listado?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button-danger" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No hay listados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
