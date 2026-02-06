<?php

namespace App\Jobs;

use App\Models\MessageLog;
use App\Services\WabaClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendTemplateMessage implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(
        public int $empleadoId,
        public string $phoneNumber,
        public string $templateName,
        public ?string $templateLanguage,
        public array $components = [],
        public ?int $scheduledMessageId = null
    ) {
    }

    public function handle(WabaClient $client): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $response = $client->sendTemplateMessage(
                $this->phoneNumber,
                $this->templateName,
                (string) $this->templateLanguage,
                $this->components
            );

            MessageLog::create([
                'empleado_id' => $this->empleadoId,
                'template_name' => $this->templateName,
                'template_language' => $this->templateLanguage,
                'status' => 'sent',
                'response' => $response,
                'sent_at' => now(),
                'scheduled_message_id' => $this->scheduledMessageId,
            ]);
        } catch (RequestException $exception) {
            $response = $exception->response?->json();

            MessageLog::create([
                'empleado_id' => $this->empleadoId,
                'template_name' => $this->templateName,
                'template_language' => $this->templateLanguage,
                'status' => 'failed',
                'response' => $response,
                'error' => $exception->getMessage(),
                'sent_at' => now(),
                'scheduled_message_id' => $this->scheduledMessageId,
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            MessageLog::create([
                'empleado_id' => $this->empleadoId,
                'template_name' => $this->templateName,
                'template_language' => $this->templateLanguage,
                'status' => 'failed',
                'error' => $exception->getMessage(),
                'sent_at' => now(),
                'scheduled_message_id' => $this->scheduledMessageId,
            ]);

            throw $exception;
        }
    }
}
