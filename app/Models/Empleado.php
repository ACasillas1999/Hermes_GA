<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Puesto',
        'Nombre',
        'Numero',
        'listado_id',
    ];

    public function listado()
    {
        return $this->belongsTo(Listado::class, 'listado_id');
    }
}
