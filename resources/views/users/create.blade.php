@extends('layouts.app')

@section('title', $isFirst ? 'Crear primer usuario' : 'Nuevo usuario')
@section('subtitle', $isFirst ? 'Configura el acceso inicial' : 'Agrega un nuevo usuario')

@section('header-actions')
    @if (!$isFirst)
        <a class="button button-secondary" href="{{ route('users.index') }}">Volver</a>
    @endif
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="row">
                <label for="name">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="row">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="row-inline">
                <div style="flex: 1;">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div style="flex: 1;">
                    <label for="password_confirmation">Confirmar password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
            </div>
            <div class="row-inline">
                <button class="button" type="submit">{{ $isFirst ? 'Crear y entrar' : 'Crear usuario' }}</button>
            </div>
        </form>
    </div>
@endsection
