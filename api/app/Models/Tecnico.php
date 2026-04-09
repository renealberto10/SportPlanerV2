<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tecnico extends Model {
    protected $fillable = ['nombre', 'apellido', 'telefono', 'email', 'especialidad', 'activo', 'notas'];
    protected $casts    = ['activo' => 'boolean'];

    public function getNombreCompletoAttribute(): string {
        return "{$this->nombre} {$this->apellido}";
    }

    public function mantenimientos(): HasMany {
        return $this->hasMany(Mantenimiento::class);
    }
}
