@extends('layouts.app')

@section('title', 'Usuarios')
@section('subtitle', 'Gestion de usuarios del sistema')

@section('header-actions')
    <a class="button" href="{{ route('users.create') }}">Nuevo usuario</a>
@endsection

@section('content')
    <div class="card">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="row-inline">
                                    <a class="button button-secondary" href="{{ route('users.edit', $user) }}">Editar</a>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Eliminar usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button button-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No hay usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 12px;">
            {{ $users->links() }}
        </div>
    </div>
@endsection
