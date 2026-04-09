<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CambioPieza extends Model
{
    protected $table = 'cambios_piezas';

    protected $fillable = [
        'mantenimiento_id', 'evento_id', 'escenario_id', 'equipo_id',
        'descripcion_pieza', 'tipo_pieza', 'serie_instalada', 'serie_retirada',
        'tecnico_id', 'fecha', 'estado_bodega', 'recibido_por', 'fecha_recepcion', 'notas',
    ];

    public function escenario()     { return $this->belongsTo(Escenario::class); }
    public function equipo()        { return $this->belongsTo(Equipo::class); }
    public function tecnico()       { return $this->belongsTo(Tecnico::class); }
    public function mantenimiento() { return $this->belongsTo(Mantenimiento::class); }
    public function evento()        { return $this->belongsTo(Evento::class); }
}
