<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WabaTemplate extends Model
{
    protected $table = 'waba_templates';

    protected $fillable = [
        'meta_template_id',
        'waba_id',
        'name',
        'language',
        'status',
        'category',
        'header_type',
        'header_text',
        'components',
        'quality_score',
        'last_synced_at',
    ];

    protected $casts = [
        'components' => 'array',
        'quality_score' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function bodyText(): ?string
    {
        foreach ($this->components ?? [] as $component) {
            if (($component['type'] ?? '') === 'BODY') {
                return $component['text'] ?? null;
            }
        }

        return null;
    }

    public function bodyParameterCount(): int
    {
        $text = $this->bodyText();

        if (!$text) {
            return 0;
        }

        preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);

        if (empty($matches[1])) {
            return 0;
        }

        $indexes = array_map('intval', $matches[1]);

        return max($indexes);
    }

    public function headerComponent(): ?array
    {
        foreach ($this->components ?? [] as $component) {
            if (strtoupper($component['type'] ?? '') === 'HEADER') {
                return $component;
            }
        }

        return null;
    }

    public function headerFormat(): ?string
    {
        if (!empty($this->header_type)) {
            return $this->header_type;
        }

        return $this->headerComponent()['format'] ?? null;
    }

    public function headerText(): ?string
    {
        if (!empty($this->header_text)) {
            return $this->header_text;
        }

        return $this->headerComponent()['text'] ?? null;
    }

    public function headerParameterCount(): int
    {
        $format = strtoupper((string) $this->headerFormat());

        if ($format === 'TEXT') {
            $text = $this->headerText();
            if (!$text) {
                return 0;
            }

            preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);

            if (empty($matches[1])) {
                return 0;
            }

            $indexes = array_map('intval', $matches[1]);

            return max($indexes);
        }

        if (in_array($format, ['IMAGE', 'VIDEO', 'DOCUMENT'], true)) {
            return 1;
        }

        return 0;
    }

    public function headerRequiresMedia(): bool
    {
        return in_array(strtoupper((string) $this->headerFormat()), ['IMAGE', 'VIDEO', 'DOCUMENT'], true);
    }
}
