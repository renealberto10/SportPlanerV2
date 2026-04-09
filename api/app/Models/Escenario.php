<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escenario extends Model
{
    protected $fillable = [
        'nombre', 'descripcion', 'direccion', 'activo', 'tiene_colosseo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'tiene_colosseo' => 'boolean',
    ];

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class);
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }
}
