<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WabaClient
{
    protected string $baseUrl;
    protected string $token;
    protected string $phoneNumberId;
    protected string $accountId;

    public function __construct()
    {
        $baseUrl = rtrim((string) config('waba.base_url'), '/');
        $version = (string) config('waba.version');

        $this->baseUrl = $baseUrl.'/'.$version;
        $this->token = (string) config('waba.token');
        $this->phoneNumberId = (string) config('waba.phone_number_id');
        $this->accountId = (string) config('waba.account_id');
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws RequestException
     */
    public function listTemplates(): array
    {
        $this->guardConfig();

        $templates = [];
        $url = $this->baseUrl.'/'.$this->accountId.'/message_templates';
        $params = ['limit' => 200];

        while ($url) {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->timeout(30)
                ->get($url, $params);

            $response->throw();

            $payload = $response->json();
            $templates = array_merge($templates, $payload['data'] ?? []);

            $url = $payload['paging']['next'] ?? null;
            $params = [];
        }

        return $templates;
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     *
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function sendTemplateMessage(string $to, string $templateName, string $language, array $components = []): array
    {
        $this->guardConfig();

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language,
                ],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->timeout(30)
            ->post($this->baseUrl.'/'.$this->phoneNumberId.'/messages', $payload);

        $response->throw();

        return $response->json();
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     *
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function createTemplate(string $name, string $language, string $category, array $components): array
    {
        $this->guardConfig();

        $payload = [
            'name' => $name,
            'language' => $language,
            'category' => $category,
            'components' => $components,
        ];

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->timeout(30)
            ->post($this->baseUrl.'/'.$this->accountId.'/message_templates', $payload);

        $response->throw();

        return $response->json();
    }

    protected function guardConfig(): void
    {
        if ($this->token === '' || $this->phoneNumberId === '' || $this->accountId === '') {
            throw new RuntimeException('Faltan credenciales de WABA en el archivo .env.');
        }
    }
}
