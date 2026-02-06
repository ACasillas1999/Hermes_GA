<?php

namespace App\Http\Controllers;

use App\Models\Listado;
use App\Models\MessageLog;
use Illuminate\Http\Request;

class MessageLogController extends Controller
{
    public function index(Request $request)
    {
        $query = MessageLog::query()
            ->with(['empleado.listado'])
            ->orderByDesc('sent_at')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('template')) {
            $query->where('template_name', $request->string('template'));
        }

        if ($request->filled('listado_id')) {
            $listadoId = (int) $request->input('listado_id');
            $query->whereHas('empleado', function ($empleadoQuery) use ($listadoId) {
                $empleadoQuery->where('listado_id', $listadoId);
            });
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($sub) use ($term) {
                $sub->where('template_name', 'like', '%'.$term.'%')
                    ->orWhereHas('empleado', function ($empleadoQuery) use ($term) {
                        $empleadoQuery->where('Nombre', 'like', '%'.$term.'%')
                            ->orWhere('Numero', 'like', '%'.$term.'%');
                    });
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('sent_at', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('sent_at', '<=', $request->string('to'));
        }

        $logs = $query->paginate(50)->withQueryString();

        $templates = MessageLog::query()
            ->select('template_name')
            ->distinct()
            ->orderBy('template_name')
            ->pluck('template_name');

        $listados = Listado::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('history.index', [
            'logs' => $logs,
            'templates' => $templates,
            'listados' => $listados,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'template' => $request->string('template')->toString(),
                'listado_id' => $request->string('listado_id')->toString(),
                'q' => $request->string('q')->toString(),
                'from' => $request->string('from')->toString(),
                'to' => $request->string('to')->toString(),
            ],
        ]);
    }
}
