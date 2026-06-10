<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'resend_id',
        'name',
        'subject',
        'html_body',
    ];

    public function getVariablesAttribute(): array
    {
        if (!$this->html_body) {
            return [];
        }

        preg_match_all('/\{\{\{?\s*([a-zA-Z0-9_]+)\s*\}?\}\}/', $this->html_body, $matches);
        return array_values(array_unique($matches[1] ?? []));
    }
}
