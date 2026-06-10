<?php

namespace App\Jobs;

use App\Models\Empleado;
use App\Models\EmailTemplate;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailMessage implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $empleadoId;
    public $correo;
    public $templateId;
    public $customSubject;
    public $fromAddress;
    public $fromName;
    public $params;

    /**
     * Create a new job instance.
     */
    public function __construct($empleadoId, $correo, $templateId, $customSubject = null, $fromAddress = null, $fromName = null, $params = [])
    {
        $this->empleadoId = $empleadoId;
        $this->correo = $correo;
        $this->templateId = $templateId;
        $this->customSubject = $customSubject;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $template = EmailTemplate::find($this->templateId);
        if (!$template || !$this->correo) {
            return;
        }

        $subject = $this->customSubject ?: $template->subject ?: 'Notificación';
        $fromEmail = $this->fromAddress ?: env('MAIL_FROM_ADDRESS', 'onboarding@resend.dev');
        $fromNameText = $this->fromName ?: env('MAIL_FROM_NAME', 'Hermes GA');

        $html = $template->html_body;
        foreach ($this->params as $key => $value) {
            $html = preg_replace('/\{\{\{?\s*' . preg_quote($key, '/') . '\s*\}?\}\}/', htmlspecialchars((string) $value), $html);
        }

        try {
            if ($template->resend_id) {
                // Send directly via Resend API using the synced HTML
                $response = \Illuminate\Support\Facades\Http::withToken(env('RESEND_API_KEY'))
                    ->post('https://api.resend.com/emails', [
                        'from' => $fromNameText . ' <' . $fromEmail . '>',
                        'to' => [$this->correo],
                        'subject' => $subject,
                        'html' => $html,
                    ]);

                if ($response->failed()) {
                    throw new \Exception('Resend API Error: ' . $response->body());
                }

                $responseData = $response->json();
            } else {
                // Send locally using Laravel Mail
                Mail::html($html, function ($message) use ($subject, $fromEmail, $fromNameText) {
                    $message->from($fromEmail, $fromNameText)
                            ->to($this->correo)
                            ->subject($subject);
                });
                
                $responseData = ['status' => 'sent_via_smtp'];
            }

            \App\Models\MessageLog::create([
                'empleado_id' => $this->empleadoId,
                'template_name' => $template->name . ' (Correo)',
                'template_language' => null,
                'status' => 'sent',
                'response' => $responseData,
                'sent_at' => now(),
            ]);

        } catch (\Throwable $e) {
            \App\Models\MessageLog::create([
                'empleado_id' => $this->empleadoId,
                'template_name' => $template->name . ' (Correo)',
                'template_language' => null,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
