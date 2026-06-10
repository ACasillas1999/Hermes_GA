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
            'correo' => ['nullable', 'email', 'max:150'],
        ]);

        Empleado::create([
            'Puesto' => $listado->nombre,
            'Nombre' => trim($data['nombre']),
            'Numero' => trim($data['numero']),
            'Correo' => isset($data['correo']) ? trim($data['correo']) : null,
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
            'correo' => ['nullable', 'email', 'max:150'],
        ]);

        $empleado->update([
            'Puesto' => $listado->nombre,
            'Nombre' => trim($data['nombre']),
            'Numero' => trim($data['numero']),
            'Correo' => isset($data['correo']) ? trim($data['correo']) : null,
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

    public function importCsv(Request $request, Listado $listado)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file = $request->file('csv_file');
        
        $handle = fopen($file->getRealPath(), "r");
        if ($handle !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            
            // Expected columns (flexible check could be done, assuming Name, Phone, Email)
            // But we will just try to read rows assuming order or checking headers if needed.
            // Let's assume order: Nombre, Numero, Correo OR we check column names
            
            $headerMap = [];
            foreach ($header as $index => $colName) {
                $colName = strtolower(trim($colName));
                if (str_contains($colName, 'nombre') || str_contains($colName, 'name')) {
                    $headerMap['nombre'] = $index;
                } elseif (str_contains($colName, 'numero') || str_contains($colName, 'telefono') || str_contains($colName, 'phone')) {
                    $headerMap['numero'] = $index;
                } elseif (str_contains($colName, 'correo') || str_contains($colName, 'email')) {
                    $headerMap['correo'] = $index;
                }
            }
            
            $count = 0;
            DB::beginTransaction();
            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Try to map by header, otherwise use default order: 0=Nombre, 1=Numero, 2=Correo
                    $nombre = isset($headerMap['nombre']) ? ($data[$headerMap['nombre']] ?? '') : ($data[0] ?? '');
                    $numero = isset($headerMap['numero']) ? ($data[$headerMap['numero']] ?? '') : ($data[1] ?? '');
                    $correo = isset($headerMap['correo']) ? ($data[$headerMap['correo']] ?? '') : ($data[2] ?? '');
                    
                    if (trim($nombre) === '' && trim($numero) === '' && trim($correo) === '') {
                        continue;
                    }

                    Empleado::updateOrCreate(
                        [
                            'listado_id' => $listado->id,
                            'Numero' => trim($numero),
                        ],
                        [
                            'Puesto' => $listado->nombre,
                            'Nombre' => trim($nombre),
                            'Correo' => trim($correo) ?: null,
                        ]
                    );
                    $count++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                return back()->withErrors(['csv_file' => 'Error al procesar el archivo CSV: ' . $e->getMessage()]);
            }
            
            fclose($handle);
            
            return redirect()
                ->route('listados.show', $listado)
                ->with('status', "Se han importado $count contactos correctamente.");
        }

        return back()->withErrors(['csv_file' => 'No se pudo abrir el archivo CSV.']);
    }
}
