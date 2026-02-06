<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listado extends Model
{
    protected $table = 'listados';

    protected $fillable = [
        'nombre',
    ];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'listado_id');
    }
}
