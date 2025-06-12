<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $fillable = ['nombre', 'clave', 'activo'];

    public function permisos()
    {
        return $this->hasMany(Permiso::class);
    }
}
