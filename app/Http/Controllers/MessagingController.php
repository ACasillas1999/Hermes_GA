<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Jobs\SendTemplateMessage;
use App\Models\Listado;
use App\Models\MessageLog;
use App\Models\WabaTemplate;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendEmailMessage;

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

        $emailTemplates = EmailTemplate::query()
            ->orderByDesc('created_at')
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
            'emailTemplates' => $emailTemplates,
            'emailTemplatesForJs' => $emailTemplates->map(function ($t) {
                return [
                    'id' => $t->id,
                    'subject' => $t->subject,
                    'html_body' => $t->html_body,
                    'variables' => $t->variables,
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
            'send_type' => ['required', 'in:whatsapp,email'],
            'listado_id' => ['required', 'integer', 'exists:listados,id'],
            'template_id' => ['required_if:send_type,whatsapp', 'nullable', 'integer', 'exists:waba_templates,id'],
            'email_template_id' => ['required_if:send_type,email', 'nullable', 'integer', 'exists:email_templates,id'],
            'email_subject' => ['nullable', 'string', 'max:255'],
            'email_from_address' => ['nullable', 'email', 'max:255'],
            'email_from_name' => ['nullable', 'string', 'max:255'],
            'email_params' => ['array'],
            'email_params.*' => ['nullable', 'string', 'max:1024'],
            'body_params' => ['array'],
            'body_params.*' => ['nullable', 'string', 'max:1024'],
            'header_media_url' => ['nullable', 'url', 'max:2048'],
            'header_text_params' => ['array'],
            'header_text_params.*' => ['nullable', 'string', 'max:1024'],
        ]);

        $listado = Listado::findOrFail($validated['listado_id']);

        $empleados = Empleado::query()
            ->where('listado_id', $listado->id)
            ->get(['ID', 'Nombre', 'Numero', 'Correo']);

        if ($empleados->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'listado_id' => 'No hay empleados en ese listado.',
                ]);
        }

        if ($validated['send_type'] === 'email') {
            $template = EmailTemplate::findOrFail($validated['email_template_id']);
            $empleadosConCorreo = $empleados->filter(fn($e) => !empty($e->Correo));
            
            if ($empleadosConCorreo->isEmpty()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'listado_id' => 'No hay empleados con correo electrónico en ese listado.',
                    ]);
            }

            $fromEmail   = $validated['email_from_address'] ?? config('mail.from.address');
            $fromName    = $validated['email_from_name']    ?? config('mail.from.name');
            $subject     = $validated['email_subject'] ?? $template->subject ?? 'Notificación';
            $params      = $validated['email_params'] ?? [];

            $html = $template->html_body;
            foreach ($params as $key => $value) {
                $html = preg_replace('/\{\{\{?\s*' . preg_quote($key, '/') . '\s*\}?\}\}/', htmlspecialchars((string) $value), $html);
            }

            $sent   = 0;
            $failed = 0;

            foreach ($empleadosConCorreo as $empleado) {
                try {
                    // Merge contact data so {{nombre}}, {{correo}}, etc. are replaced automatically
                    $contactData = [
                        'nombre'  => $empleado->Nombre,
                        'Nombre'  => $empleado->Nombre,
                        'name'    => $empleado->Nombre,
                        'correo'  => $empleado->Correo,
                        'Correo'  => $empleado->Correo,
                        'email'   => $empleado->Correo,
                        'numero'  => $empleado->Numero,
                        'Numero'  => $empleado->Numero,
                        'phone'   => $empleado->Numero,
                        'puesto'  => $empleado->Puesto ?? '',
                        'Puesto'  => $empleado->Puesto ?? '',
                    ];

                    // User-provided params override contact data
                    $finalParams = array_merge($contactData, $params);

                    $personalizedHtml = $html;
                    foreach ($finalParams as $key => $value) {
                        $personalizedHtml = preg_replace(
                            '/\{\{\{?\s*' . preg_quote($key, '/') . '\s*\}?\}\}/',
                            htmlspecialchars((string) $value),
                            $personalizedHtml
                        );
                    }

                    $response = \Illuminate\Support\Facades\Http::withToken(env('RESEND_API_KEY'))
                        ->post('https://api.resend.com/emails', [
                            'from'    => $fromName . ' <' . $fromEmail . '>',
                            'to'      => [$empleado->Correo],
                            'subject' => $subject,
                            'html'    => $personalizedHtml,
                        ]);

                    if ($response->failed()) {
                        throw new \Exception('Resend error: ' . $response->body());
                    }

                    \App\Models\MessageLog::create([
                        'empleado_id'       => $empleado->ID,
                        'template_name'     => $template->name . ' (Correo)',
                        'template_language' => null,
                        'status'            => 'sent',
                        'response'          => $response->json(),
                        'sent_at'           => now(),
                    ]);

                    $sent++;
                } catch (\Throwable $e) {
                    \App\Models\MessageLog::create([
                        'empleado_id'       => $empleado->ID,
                        'template_name'     => $template->name . ' (Correo)',
                        'template_language' => null,
                        'status'            => 'failed',
                        'error'             => $e->getMessage(),
                        'sent_at'           => now(),
                    ]);
                    $failed++;
                }
            }

            return redirect()
                ->route('messaging.index')
                ->with('status', "Envío completado: {$sent} correos enviados, {$failed} fallidos.");
        }

        $template = WabaTemplate::findOrFail($validated['template_id']);
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
