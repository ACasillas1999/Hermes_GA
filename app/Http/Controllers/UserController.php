<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $isFirst = !User::query()->exists();

        if (!$isFirst && !Auth::check()) {
            return redirect()->route('login');
        }

        return view('users.create', [
            'isFirst' => $isFirst,
        ]);
    }

    public function store(Request $request)
    {
        $isFirst = !User::query()->exists();

        if (!$isFirst && !Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create($validated);

        if ($isFirst) {
            Auth::login($user);
            return redirect()->route('dashboard')->with('status', 'Usuario administrador creado.');
        }

        return redirect()->route('users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('status', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['delete' => 'No puedes eliminar tu propio usuario.']);
        }

        if (User::query()->count() <= 1) {
            return back()->withErrors(['delete' => 'Debe existir al menos un usuario.']);
        }

        $user->delete();

        return back()->with('status', 'Usuario eliminado.');
    }
}
