<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role'); // solo si ya la tienes y la vas a eliminar

            // si quieres registrar la relación manualmente (aunque usas tabla pivot)
            // $table->foreignId('nivel_id')->nullable()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'empleado'])->default('empleado'); // restaurar si haces rollback
        });
    }
};
