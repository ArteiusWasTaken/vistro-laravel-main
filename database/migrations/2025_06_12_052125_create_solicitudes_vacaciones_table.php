<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('solicitudes_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Empleado
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('motivo')->nullable();
            $table->enum('estado', ['pendiente_rrhh', 'pendiente_jefe', 'aprobada', 'rechazada'])->default('pendiente_rrhh');
            $table->foreignId('aprobado_por_rrhh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por_jefe')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_vacaciones');
    }
};
