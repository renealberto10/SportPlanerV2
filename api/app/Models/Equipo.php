<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipo extends Model
{
    protected $fillable = [
        'escenario_id', 'nombre', 'tipo', 'modelo', 'serie',
        'estado', 'fecha_instalacion', 'notas',
    ];

    public function escenario(): BelongsTo
    {
        return $this->belongsTo(Escenario::class);
    }
}
