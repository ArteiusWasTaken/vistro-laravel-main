<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    protected $table = 'niveles';
    protected $fillable = ['nombre', 'descripcion'];

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'nivel_permiso');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_nivel');
    }
}
