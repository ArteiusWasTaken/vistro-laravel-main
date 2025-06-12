<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $niveles = ['admin', 'rrhh', 'jefe', 'empleado'];

        foreach ($niveles as $nivel) {
            Nivel::firstOrCreate(['nombre' => $nivel]);
        }
    }
}
