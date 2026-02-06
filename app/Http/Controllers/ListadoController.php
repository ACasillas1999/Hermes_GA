<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Listado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ListadoController extends Controller
{
    public function index(Request $request)
    {
        $query = Listado::query()->withCount('empleados')->orderBy('nombre');

        if ($request->filled('q')) {
            $query->where('nombre', 'like', '%'.$request->string('q').'%');
        }

        $listados = $query->get();

        return view('listados.index', [
            'listados' => $listados,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function create()
    {
        return view('listados.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:listados,nombre'],
        ]);

        $listado = Listado::create([
            'nombre' => trim($data['nombre']),
        ]);

        return redirect()
            ->route('listados.show', $listado)
            ->with('status', 'Listado creado correctamente.');
    }

    public function show(Request $request, Listado $listado)
    {
        $empleadosQuery = $listado->empleados()->orderBy('Nombre');

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $empleadosQuery->where(function ($query) use ($term) {
                $query->where('Nombre', 'like', '%'.$term.'%')
                    ->orWhere('Numero', 'like', '%'.$term.'%');
            });
        }

        $empleados = $empleadosQuery->paginate(100)->withQueryString();

        return view('listados.show', [
            'listado' => $listado,
            'empleados' => $empleados,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function edit(Listado $listado)
    {
        return view('listados.edit', [
            'listado' => $listado,
        ]);
    }

    public function update(Request $request, Listado $listado)
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('listados', 'nombre')->ignore($listado->id),
            ],
        ]);

        DB::transaction(function () use ($listado, $data) {
            $listado->update(['nombre' => trim($data['nombre'])]);

            Empleado::where('listado_id', $listado->id)
                ->update(['Puesto' => $listado->nombre]);
        });

        return redirect()
            ->route('listados.show', $listado)
            ->with('status', 'Listado actualizado.');
    }

    public function destroy(Listado $listado)
    {
        if ($listado->empleados()->exists()) {
            return back()->withErrors([
                'listado' => 'No puedes eliminar un listado con empleados. Elimina primero a los empleados.',
            ]);
        }

        $listado->delete();

        return redirect()
            ->route('listados.index')
            ->with('status', 'Listado eliminado.');
    }

    public function storeEmpleado(Request $request, Listado $listado)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'numero' => ['required', 'string', 'max:30'],
        ]);

        Empleado::create([
            'Puesto' => $listado->nombre,
            'Nombre' => trim($data['nombre']),
            'Numero' => trim($data['numero']),
            'listado_id' => $listado->id,
        ]);

        return redirect()
            ->route('listados.show', $listado)
            ->with('status', 'Persona agregada.');
    }

    public function updateEmpleado(Request $request, Listado $listado, Empleado $empleado)
    {
        if ((int) $empleado->listado_id !== (int) $listado->id) {
            abort(404);
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'numero' => ['required', 'string', 'max:30'],
        ]);

        $empleado->update([
            'Puesto' => $listado->nombre,
            'Nombre' => trim($data['nombre']),
            'Numero' => trim($data['numero']),
        ]);

        return redirect()
            ->route('listados.show', $listado)
            ->with('status', 'Persona actualizada.');
    }

    public function destroyEmpleado(Listado $listado, Empleado $empleado)
    {
        if ((int) $empleado->listado_id !== (int) $listado->id) {
            abort(404);
        }

        $empleado->delete();

        return redirect()
            ->route('listados.show', $listado)
            ->with('status', 'Persona eliminada.');
    }
}
