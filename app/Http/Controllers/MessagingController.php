<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Jobs\SendTemplateMessage;
use App\Models\Listado;
use App\Models\MessageLog;
use App\Models\WabaTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class MessagingController extends Controller
{
    public function index()
    {
        $batchId = request()->query('batch');
        $listados = Listado::query()
            ->withCount('empleados')
            ->orderBy('nombre')
            ->get();

        $templates = WabaTemplate::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $recentMessages = MessageLog::query()
            ->with(['empleado.listado'])
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('messaging.index', [
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
            'batchId' => $batchId,
            'stats' => [
                'plantillas' => $templates->count(),
                'listados' => $listados->count(),
                'empleados' => $listados->sum('empleados_count'),
                'mensajes' => MessageLog::count(),
            ],
            'recentMessages' => $recentMessages,
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'listado_id' => ['required', 'integer', 'exists:listados,id'],
            'template_id' => ['required', 'integer', 'exists:waba_templates,id'],
            'body_params' => ['array'],
            'body_params.*' => ['nullable', 'string', 'max:1024'],
            'header_media_url' => ['nullable', 'url', 'max:2048'],
            'header_text_params' => ['array'],
            'header_text_params.*' => ['nullable', 'string', 'max:1024'],
        ]);

        $template = WabaTemplate::findOrFail($validated['template_id']);
        $listado = Listado::findOrFail($validated['listado_id']);
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

        $empleados = Empleado::query()
            ->where('listado_id', $listado->id)
            ->get(['ID', 'Nombre', 'Numero']);

        if ($empleados->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'listado_id' => 'No hay empleados en ese listado.',
                ]);
        }

        $components = [];

        if ($template->headerRequiresMedia()) {
            $mediaType = strtolower($headerFormat);
            $components[] = [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => $mediaType,
                        $mediaType => [
                            'link' => $validated['header_media_url'],
                        ],
                    ],
                ],
            ];
        } elseif ($headerFormat === 'TEXT' && $headerRequiredCount > 0) {
            $headerTextParams = array_values(array_filter(
                $validated['header_text_params'] ?? [],
                fn ($value) => $value !== null && $value !== ''
            ));

            $components[] = [
                'type' => 'header',
                'parameters' => array_map(
                    fn ($value) => ['type' => 'text', 'text' => $value],
                    array_slice($headerTextParams, 0, $headerRequiredCount)
                ),
            ];
        }

        if ($requiredCount > 0) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(
                    fn ($value) => ['type' => 'text', 'text' => $value],
                    array_slice($bodyParams, 0, $requiredCount)
                ),
            ];
        }

        $jobs = $empleados->map(function ($empleado) use ($template, $components) {
            return new SendTemplateMessage(
                $empleado->ID,
                $empleado->Numero,
                $template->name,
                $template->language,
                $components,
                null
            );
        })->all();

        $batch = Bus::batch($jobs)
            ->name('Envio plantilla '.$template->name)
            ->allowFailures()
            ->dispatch();

        return redirect()
            ->route('messaging.index', ['batch' => $batch->id])
            ->with('status', 'Envio en proceso. Puedes monitorear el progreso.');
    }

    public function batchStatus(string $id)
    {
        $batch = Bus::findBatch($id);

        if (!$batch) {
            return response()->json(['message' => 'Batch no encontrado'], 404);
        }

        return response()->json([
            'id' => $batch->id,
            'name' => $batch->name,
            'total' => $batch->totalJobs,
            'pending' => $batch->pendingJobs,
            'processed' => $batch->processedJobs(),
            'failed' => $batch->failedJobs,
            'progress' => $batch->progress(),
            'finished' => (bool) $batch->finishedAt,
        ]);
    }
}
