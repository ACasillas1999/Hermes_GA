@extends('layouts.auth')

@section('title', 'Acceso')

@section('content')
    @if (!$hasUsers)
        <div class="alert" style="background: rgba(255, 140, 0, 0.12); border-color: rgba(255, 140, 0, 0.4); color: var(--text);">
            No hay usuarios registrados. Crea el primer usuario para continuar.
        </div>
        <a class="button" href="{{ route('users.create') }}">Crear primer usuario</a>
    @else
        <form method="POST" action="{{ route('login.attempt') }}" class="auth-form">
            @csrf
            <div class="row">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="row">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div class="button-container">
                <button class="button" type="submit" style="width: 100%;">Entrar</button>
            </div>
        </form>
    @endif
@endsection
