<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudVacaciones extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'fecha_inicio', 'fecha_fin', 'motivo',
        'estado', 'aprobado_por_rrhh', 'aprobado_por_jefe'
    ];

    public function empleado() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rrhh() {
        return $this->belongsTo(User::class, 'aprobado_por_rrhh');
    }

    public function jefe() {
        return $this->belongsTo(User::class, 'aprobado_por_jefe');
    }
}
