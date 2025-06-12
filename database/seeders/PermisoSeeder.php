<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modulos = Modulo::all();

        foreach ($modulos as $modulo) {
            $acciones = ['ver', 'crear', 'editar', 'eliminar', 'aprobar'];

            foreach ($acciones as $accion) {
                Permiso::firstOrCreate([
                    'clave' => $modulo->clave . '.' . $accion,
                ], [
                    'nombre' => ucfirst($accion),
                    'modulo_id' => $modulo->id,
                ]);
            }
        }

    }
}
