@extends('layouts.app')

@section('title', 'Editar usuario')
@section('subtitle', 'Actualiza los datos del usuario')

@section('header-actions')
    <a class="button button-secondary" href="{{ route('users.index') }}">Volver</a>
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <label for="name">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="row">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="row-inline">
                <div style="flex: 1;">
                    <label for="password">Nuevo password (opcional)</label>
                    <input id="password" type="password" name="password">
                </div>
                <div style="flex: 1;">
                    <label for="password_confirmation">Confirmar password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation">
                </div>
            </div>
            <div class="row-inline">
                <button class="button" type="submit">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection
