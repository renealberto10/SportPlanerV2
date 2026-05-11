<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model {
    protected $fillable = [
        'escenario_id', 'tecnico_id', 'fecha', 'hora', 'tecnico', 'tipo',
        'actividades', 'observaciones', 'estado', 'horas', 'visitas', 'personal', 'fotos',
    ];

    protected $casts = [
        'fotos' => 'array',
    ];

    public function escenario(): BelongsTo { return $this->belongsTo(Escenario::class); }
    public function tecnicoRel(): BelongsTo { return $this->belongsTo(Tecnico::class, 'tecnico_id'); }
}
