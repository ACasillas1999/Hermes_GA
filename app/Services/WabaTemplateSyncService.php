<?php

namespace App\Services;

use App\Models\WabaTemplate;
use Illuminate\Support\Carbon;

class WabaTemplateSyncService
{
    public function __construct(private readonly WabaClient $client)
    {
    }

    /**
     * @return array{count:int, updated:int}
     */
    public function sync(): array
    {
        $templates = $this->client->listTemplates();
        $now = Carbon::now();
        $updated = 0;

        foreach ($templates as $template) {
            if (empty($template['name'])) {
                continue;
            }

            $headerType = null;
            $headerText = null;
            $components = $template['components'] ?? [];

            if (is_array($components)) {
                foreach ($components as $component) {
                    if (($component['type'] ?? '') === 'HEADER') {
                        $headerType = strtoupper($component['format'] ?? 'TEXT');
                        $headerText = $component['text'] ?? null;
                        break;
                    }
                }
            }

            WabaTemplate::updateOrCreate(
                [
                    'name' => $template['name'] ?? '',
                    'language' => $template['language'] ?? '',
                ],
                [
                    'meta_template_id' => $template['id'] ?? null,
                    'waba_id' => $this->client->accountId(),
                    'status' => $template['status'] ?? null,
                    'category' => $template['category'] ?? null,
                    'header_type' => $headerType,
                    'header_text' => $headerText,
                    'components' => $template['components'] ?? [],
                    'quality_score' => $template['quality_score'] ?? null,
                    'last_synced_at' => $now,
                ]
            );

            $updated++;
        }

        return [
            'count' => count($templates),
            'updated' => $updated,
        ];
    }
}
