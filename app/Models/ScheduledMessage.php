<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledMessage extends Model
{
    protected $table = 'scheduled_messages';

    protected $fillable = [
        'listado_id',
        'template_id',
        'body_params',
        'header_media_url',
        'header_text_params',
        'scheduled_at',
        'sent_at',
        'status',
        'batch_id',
        'error',
    ];

    protected $casts = [
        'body_params' => 'array',
        'header_text_params' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function listado(): BelongsTo
    {
        return $this->belongsTo(Listado::class, 'listado_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WabaTemplate::class, 'template_id');
    }
}
