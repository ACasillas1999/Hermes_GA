@extends('layouts.app')

@section('title', 'Editar listado')
@section('subtitle', 'Actualiza el nombre del listado')

@section('header-actions')
    <a class="button button-secondary" href="{{ route('listados.show', $listado) }}">Volver</a>
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('listados.update', $listado) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <label for="nombre">Nombre del listado</label>
                <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $listado->nombre) }}" required>
            </div>
            <div class="row-inline">
                <button class="button" type="submit">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection
