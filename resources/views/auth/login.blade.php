@extends('layouts.auth')

@section('title', 'Acceso')

@section('content')
    @if (!$hasUsers)
        <div class="alert" style="background: rgba(56, 189, 248, 0.12); border-color: rgba(56, 189, 248, 0.4); color: var(--text);">
            No hay usuarios registrados. Crea el primer usuario para continuar.
        </div>
        <a class="button" href="{{ route('users.create') }}">Crear primer usuario</a>
    @else
        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="row">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="row">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div class="row">
                <button class="button" type="submit">Entrar</button>
            </div>
        </form>
    @endif
@endsection
