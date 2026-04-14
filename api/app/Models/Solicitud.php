<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = [
        'fecha_solicitud', 'actividad', 'escenario_id', 'escenario_texto',
        'solicita', 'fecha_calendarizada', 'hora', 'tecnico_id',
        'seguimiento', 'prioridad', 'estado', 'notas', 'emails_invitar', 'fotos',
    ];

    protected $casts = ['fotos' => 'array'];

    public function escenario() { return $this->belongsTo(Escenario::class); }
    public function tecnico()   { return $this->belongsTo(Tecnico::class); }
}
