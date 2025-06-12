<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modulos = [
            ['nombre' => 'Vacaciones', 'clave' => 'vacaciones'],
            ['nombre' => 'Usuarios', 'clave' => 'usuarios'],
            ['nombre' => 'Dashboard', 'clave' => 'dashboard'],
            ['nombre' => 'Reportes', 'clave' => 'reportes'],
        ];

        foreach ($modulos as $modulo) {
            Modulo::firstOrCreate(['clave' => $modulo['clave']], $modulo);
        }
    }
}

