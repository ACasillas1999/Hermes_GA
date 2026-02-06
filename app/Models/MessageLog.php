<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    protected $table = 'message_logs';

    protected $fillable = [
        'empleado_id',
        'template_name',
        'template_language',
        'status',
        'response',
        'error',
        'sent_at',
        'scheduled_message_id',
    ];

    protected $casts = [
        'response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'ID');
    }
}
