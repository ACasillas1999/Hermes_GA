<?php

namespace App\Http\Controllers;

use App\Models\Listado;
use App\Models\MessageLog;
use App\Models\ScheduledMessage;
use App\Models\WabaTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class ScheduledMessageController extends Controller
{
    public function index()
    {
        $listados = Listado::query()
            ->withCount('empleados')
            ->orderBy('nombre')
            ->get();

        $templates = WabaTemplate::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $scheduledMessages = ScheduledMessage::query()
            ->with(['listado', 'template'])
            ->orderByDesc('scheduled_at')
            ->limit(50)
            ->get();

        return view('scheduled.index', [
            'listados' => $listados,
            'templates' => $templates,
            'templatesForJs' => $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'language' => $template->language,
                    'status' => $template->status,
                    'bodyText' => $template->bodyText(),
                    'bodyCount' => $template->bodyParameterCount(),
                    'headerFormat' => $template->headerFormat(),
                    'headerText' => $template->headerText(),
                    'headerCount' => $template->headerParameterCount(),
                    'headerRequiresMedia' => $template->headerRequiresMedia(),
                ];
            })->values(),
            'scheduledMessages' => $scheduledMessages,
            'timezone' => config('app.timezone'),
            'stats' => [
                'pendientes' => ScheduledMessage::query()
                    ->whereIn('status', ['pending', 'queueing', 'queued'])
                    ->count(),
                'enviados' => ScheduledMessage::query()
                    ->where('status', 'sent')
                    ->count(),
                'fallidos' => ScheduledMessage::query()
                    ->where('status', 'failed')
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listado_id' => ['required', 'integer', 'exists:listados,id'],
            'template_id' => ['required', 'integer', 'exists:waba_templates,id'],
            'scheduled_at' => ['required', 'date_format:Y-m-d\\TH:i'],
            'body_params' => ['array'],
            'body_params.*' => ['nullable', 'string', 'max:1024'],
            'header_media_url' => ['nullable', 'url', 'max:2048'],
            'header_text_params' => ['array'],
            'header_text_params.*' => ['nullable', 'string', 'max:1024'],
        ]);

        $template = WabaTemplate::findOrFail($validated['template_id']);
        $scheduledAt = Carbon::createFromFormat('Y-m-d\\TH:i', $validated['scheduled_at'], config('app.timezone'));

        if ($scheduledAt->isPast()) {
            return back()
                ->withInput()
                ->withErrors([
                    'scheduled_at' => 'La fecha debe ser futura (hora de Mexico).',
                ]);
        }

        $bodyParams = array_values(array_filter(
            $validated['body_params'] ?? [],
            fn ($value) => $value !== null && $value !== ''
        ));

        $requiredCount = $template->bodyParameterCount();
        $headerFormat = strtoupper((string) $template->headerFormat());
        $headerRequiredCount = $template->headerParameterCount();

        if ($template->headerRequiresMedia() && empty($validated['header_media_url'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'header_media_url' => 'Esta plantilla requiere una URL de imagen/video/documento en el encabezado.',
                ]);
        }

        if ($headerFormat === 'TEXT' && $headerRequiredCount > 0) {
            $headerTextParams = array_values(array_filter(
                $validated['header_text_params'] ?? [],
                fn ($value) => $value !== null && $value !== ''
            ));

            if (count($headerTextParams) < $headerRequiredCount) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'header_text_params' => "El encabezado requiere {$headerRequiredCount} parametro(s).",
                    ]);
            }
        }

        if ($requiredCount > 0 && count($bodyParams) < $requiredCount) {
            return back()
                ->withInput()
                ->withErrors([
                    'body_params' => "La plantilla requiere {$requiredCount} parametro(s).",
                ]);
        }

        $headerTextParams = array_values(array_filter(
            $validated['header_text_params'] ?? [],
            fn ($value) => $value !== null && $value !== ''
        ));

        ScheduledMessage::create([
            'listado_id' => $validated['listado_id'],
            'template_id' => $validated['template_id'],
            'body_params' => $requiredCount > 0 ? array_slice($bodyParams, 0, $requiredCount) : [],
            'header_media_url' => $template->headerRequiresMedia()
                ? ($validated['header_media_url'] ?? null)
                : null,
            'header_text_params' => $headerFormat === 'TEXT' && $headerRequiredCount > 0
                ? array_slice($headerTextParams, 0, $headerRequiredCount)
                : [],
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('scheduled.index')
            ->with('status', 'Envio programado correctamente.');
    }

    public function destroy(ScheduledMessage $scheduledMessage)
    {
        if (!in_array($scheduledMessage->status, ['pending', 'queueing', 'queued'], true)) {
            return back()->with('status', 'No se puede cancelar este envio.');
        }

        if ($scheduledMessage->batch_id) {
            $batch = Bus::findBatch($scheduledMessage->batch_id);
            $batch?->cancel();
        }

        $scheduledMessage->update([
            'status' => 'cancelled',
        ]);

        return back()->with('status', 'Envio programado cancelado.');
    }

    public function show(ScheduledMessage $scheduledMessage)
    {
        $usedFallback = false;
        $linkedCount = 0;
        $baseQuery = MessageLog::query()
            ->with(['empleado.listado'])
            ->where('scheduled_message_id', $scheduledMessage->id);

        if ($baseQuery->count() === 0) {
            $linkedCount = $this->backfillScheduledLogs($scheduledMessage);
            $baseQuery = MessageLog::query()
                ->with(['empleado.listado'])
                ->where('scheduled_message_id', $scheduledMessage->id);
            $usedFallback = $linkedCount > 0 && $baseQuery->count() > 0;
        }

        $logs = $baseQuery
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->paginate(100);

        $summary = [
            'sent' => MessageLog::query()
                ->where('scheduled_message_id', $scheduledMessage->id)
                ->where('status', 'sent')
                ->count(),
            'failed' => MessageLog::query()
                ->where('scheduled_message_id', $scheduledMessage->id)
                ->where('status', 'failed')
                ->count(),
        ];

        return view('scheduled.show', [
            'scheduledMessage' => $scheduledMessage->load(['listado', 'template']),
            'logs' => $logs,
            'summary' => $summary,
            'usedFallback' => $usedFallback,
            'linkedCount' => $linkedCount,
        ]);
    }

    private function backfillScheduledLogs(ScheduledMessage $scheduledMessage): int
    {
        $template = $scheduledMessage->template;
        if (!$template) {
            return 0;
        }

        $baseQuery = MessageLog::query()
            ->whereNull('scheduled_message_id')
            ->where('template_name', $template->name)
            ->whereHas('empleado', function ($empleadoQuery) use ($scheduledMessage) {
                $empleadoQuery->where('listado_id', $scheduledMessage->listado_id);
            });

        if (!empty($template->language)) {
            $baseQuery->where('template_language', $template->language);
        }

        if (!empty($scheduledMessage->batch_id)) {
            $batchCreatedAt = DB::table('job_batches')
                ->where('id', $scheduledMessage->batch_id)
                ->value('created_at');

            if ($batchCreatedAt) {
                $center = Carbon::createFromTimestampUTC((int) $batchCreatedAt);
                $windowStart = $center->copy()->subHours(3);
                $windowEnd = $center->copy()->addHours(3);
                $updated = (clone $baseQuery)
                    ->whereBetween(DB::raw('COALESCE(sent_at, created_at)'), [$windowStart, $windowEnd])
                    ->update(['scheduled_message_id' => $scheduledMessage->id]);

                if ($updated > 0) {
                    return $updated;
                }
            }
        }

        $scheduledAt = $scheduledMessage->scheduled_at
            ? $scheduledMessage->scheduled_at->copy()
            : null;

        if ($scheduledAt) {
            $windowStart = $scheduledAt->copy()->subHours(12);
            $windowEnd = $scheduledAt->copy()->addHours(12);

            $updated = (clone $baseQuery)
                ->whereBetween(DB::raw('COALESCE(sent_at, created_at)'), [$windowStart, $windowEnd])
                ->update(['scheduled_message_id' => $scheduledMessage->id]);

            if ($updated > 0) {
                return $updated;
            }

            $updated = (clone $baseQuery)
                ->whereDate(DB::raw('COALESCE(sent_at, created_at)'), $scheduledAt->toDateString())
                ->update(['scheduled_message_id' => $scheduledMessage->id]);

            return $updated;
        }

        return 0;
    }
}
