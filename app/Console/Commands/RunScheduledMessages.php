<?php

namespace App\Console\Commands;

use App\Jobs\SendTemplateMessage;
use App\Models\Empleado;
use App\Models\ScheduledMessage;
use App\Models\WabaTemplate;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Throwable;

class RunScheduledMessages extends Command
{
    protected $signature = 'messages:run-schedule {--limit=5}';

    protected $description = 'Dispatch scheduled message batches that are due.';

    public function handle(): int
    {
        $limit = max(1, (int) ($this->option('limit') ?? 5));
        $now = now();

        $scheduled = collect();

        DB::transaction(function () use ($now, $limit, &$scheduled) {
            $scheduled = ScheduledMessage::query()
                ->where('status', 'pending')
                ->where('scheduled_at', '<=', $now)
                ->orderBy('scheduled_at')
                ->limit($limit)
                ->lockForUpdate()
                ->get();

            foreach ($scheduled as $item) {
                $item->status = 'queueing';
                $item->save();
            }
        });

        if ($scheduled->isEmpty()) {
            $this->info('No hay envios programados.');
            return 0;
        }

        foreach ($scheduled as $item) {
            $this->dispatchScheduledMessage($item);
        }

        return 0;
    }

    private function dispatchScheduledMessage(ScheduledMessage $scheduled): void
    {
        $template = WabaTemplate::find($scheduled->template_id);

        if (!$template) {
            $scheduled->update([
                'status' => 'failed',
                'sent_at' => now(),
                'error' => 'Plantilla no encontrada.',
            ]);
            return;
        }

        $bodyParams = array_values(array_filter(
            $scheduled->body_params ?? [],
            fn ($value) => $value !== null && $value !== ''
        ));

        $requiredCount = $template->bodyParameterCount();
        $headerFormat = strtoupper((string) $template->headerFormat());
        $headerRequiredCount = $template->headerParameterCount();

        if ($template->headerRequiresMedia() && empty($scheduled->header_media_url)) {
            $scheduled->update([
                'status' => 'failed',
                'sent_at' => now(),
                'error' => 'La plantilla requiere URL de encabezado.',
            ]);
            return;
        }

        $headerTextParams = array_values(array_filter(
            $scheduled->header_text_params ?? [],
            fn ($value) => $value !== null && $value !== ''
        ));

        if ($headerFormat === 'TEXT' && $headerRequiredCount > 0 && count($headerTextParams) < $headerRequiredCount) {
            $scheduled->update([
                'status' => 'failed',
                'sent_at' => now(),
                'error' => 'Faltan parametros para el encabezado.',
            ]);
            return;
        }

        if ($requiredCount > 0 && count($bodyParams) < $requiredCount) {
            $scheduled->update([
                'status' => 'failed',
                'sent_at' => now(),
                'error' => 'Faltan parametros para el cuerpo.',
            ]);
            return;
        }

        $empleados = Empleado::query()
            ->where('listado_id', $scheduled->listado_id)
            ->get(['ID', 'Numero']);

        if ($empleados->isEmpty()) {
            $scheduled->update([
                'status' => 'failed',
                'sent_at' => now(),
                'error' => 'No hay personas en el listado.',
            ]);
            return;
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
                            'link' => $scheduled->header_media_url,
                        ],
                    ],
                ],
            ];
        } elseif ($headerFormat === 'TEXT' && $headerRequiredCount > 0) {
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

        $scheduledId = $scheduled->id;
        $jobs = $empleados->map(function ($empleado) use ($template, $components, $scheduledId) {
            return new SendTemplateMessage(
                $empleado->ID,
                $empleado->Numero,
                $template->name,
                $template->language,
                $components,
                $scheduledId
            );
        })->all();

        $listadoId = $scheduled->listado_id;
        $templateName = $template->name;
        $templateLanguage = $template->language;
        $scheduledAt = $scheduled->scheduled_at?->copy();

        $batch = Bus::batch($jobs)
            ->name('Envio programado '.$template->name)
            ->allowFailures()
            ->catch(function (Batch $batch, Throwable $exception) use ($scheduledId) {
                ScheduledMessage::query()
                    ->whereKey($scheduledId)
                    ->update([
                        'error' => $exception->getMessage(),
                    ]);
            })
            ->finally(function (Batch $batch) use ($scheduledId, $listadoId, $templateName, $templateLanguage, $scheduledAt) {
                $status = $batch->hasFailures() ? 'failed' : 'sent';
                $existingError = ScheduledMessage::query()
                    ->whereKey($scheduledId)
                    ->value('error');
                $error = $batch->hasFailures()
                    ? ($existingError ?: 'Algunos mensajes fallaron ('.$batch->failedJobs.' de '.$batch->totalJobs.').')
                    : null;

                if ($scheduledAt) {
                    $windowStart = $scheduledAt->copy()->subHours(6);
                    $windowEnd = $scheduledAt->copy()->addHours(6);

                    $query = \App\Models\MessageLog::query()
                        ->whereNull('scheduled_message_id')
                        ->where('template_name', $templateName)
                        ->whereHas('empleado', function ($empleadoQuery) use ($listadoId) {
                            $empleadoQuery->where('listado_id', $listadoId);
                        });

                    if (!empty($templateLanguage)) {
                        $query->where('template_language', $templateLanguage);
                    }

                    $query->whereBetween(DB::raw('COALESCE(sent_at, created_at)'), [$windowStart, $windowEnd])
                        ->update(['scheduled_message_id' => $scheduledId]);
                }

                ScheduledMessage::query()
                    ->whereKey($scheduledId)
                    ->update([
                        'status' => $status,
                        'sent_at' => now(),
                        'error' => $error,
                    ]);
            })
            ->dispatch();

        $scheduled->update([
            'status' => 'queued',
            'batch_id' => $batch->id,
        ]);
    }
}
