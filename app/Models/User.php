<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function niveles()
    {
        return $this->belongsToMany(Nivel::class, 'user_nivel');
    }

    public function permisos()
    {
        return $this->niveles()->with('permisos')->get()
            ->pluck('permisos')->flatten()->unique('id');
    }

    public function hasPermiso($clave)
    {
        return $this->permisos()->contains('clave', $clave);
    }
}
