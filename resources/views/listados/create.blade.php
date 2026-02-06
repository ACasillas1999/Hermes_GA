@extends('layouts.app')

@section('title', 'Nuevo listado')
@section('subtitle', 'Crea un listado para agrupar personas')

@section('header-actions')
    <a class="button button-secondary" href="{{ route('listados.index') }}">Volver</a>
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('listados.store') }}">
            @csrf
            <div class="row">
                <label for="nombre">Nombre del listado</label>
                <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" required>
            </div>
            <div class="row-inline">
                <button class="button" type="submit">Crear listado</button>
            </div>
        </form>
    </div>
@endsection
