<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evento extends Model
{
    protected $fillable = [
        'escenario_id', 'nombre', 'fecha', 'hora', 'tipo',
        'estado', 'descripcion', 'personal', 'equipos_notas',
    ];

    public function escenario(): BelongsTo
    {
        return $this->belongsTo(Escenario::class);
    }
}
