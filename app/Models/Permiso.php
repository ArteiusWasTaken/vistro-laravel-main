<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $fillable = ['nombre', 'clave', 'modulo_id'];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function niveles()
    {
        return $this->belongsToMany(Nivel::class, 'nivel_permiso');
    }
}
