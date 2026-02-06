<?php

namespace App\Http\Controllers;

use App\Models\Listado;
use App\Models\MessageLog;
use App\Models\WabaTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->string('view')->toString();
        $viewMode = in_array($view, ['calendar', 'history'], true) ? $view : 'calendar';
        $monthInput = $request->string('month')->toString();
        $selectedDateInput = $request->string('date')->toString();
        $month = $monthInput && preg_match('/^\d{4}-\d{2}$/', $monthInput)
            ? Carbon::createFromFormat('Y-m', $monthInput)
            : Carbon::now();

        $monthStart = $month->copy()->startOfMonth()->startOfDay();
        $monthEnd = $month->copy()->endOfMonth()->endOfDay();

        $counts = MessageLog::query()
            ->selectRaw('DATE(COALESCE(sent_at, created_at)) as day, COUNT(*) as total')
            ->whereBetween(DB::raw('COALESCE(sent_at, created_at)'), [$monthStart, $monthEnd])
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::MONDAY)->addDays(6);

        $weeks = [];
        $cursor = $calendarStart->copy();
        while ($cursor->lte($calendarEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dayKey = $cursor->toDateString();
                $week[] = [
                    'date' => $dayKey,
                    'label' => $cursor->day,
                    'inMonth' => $cursor->month === $month->month,
                    'count' => $counts[$dayKey] ?? 0,
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        $selectedDate = null;
        if ($selectedDateInput && preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDateInput)) {
            $selectedDate = Carbon::createFromFormat('Y-m-d', $selectedDateInput);
        }

        $dayLogs = collect();
        if ($selectedDate) {
            $dayLogs = MessageLog::query()
                ->with(['empleado.listado'])
                ->whereDate(DB::raw('COALESCE(sent_at, created_at)'), $selectedDate->toDateString())
                ->orderByDesc('sent_at')
                ->orderByDesc('id')
                ->limit(200)
                ->get();
        }

        $listados = Listado::query()
            ->withCount('empleados')
            ->get();

        $templatesCount = WabaTemplate::count();

        $recentMessages = MessageLog::query()
            ->with(['empleado.listado'])
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('dashboard.index', [
            'stats' => [
                'plantillas' => $templatesCount,
                'listados' => $listados->count(),
                'empleados' => $listados->sum('empleados_count'),
                'mensajes' => MessageLog::count(),
            ],
            'recentMessages' => $recentMessages,
            'calendar' => [
                'monthLabel' => $month->translatedFormat('F Y'),
                'monthValue' => $month->format('Y-m'),
                'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
                'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
                'weeks' => $weeks,
            ],
            'selectedDate' => $selectedDate?->toDateString(),
            'dayLogs' => $dayLogs,
            'viewMode' => $viewMode,
        ]);
    }
}
