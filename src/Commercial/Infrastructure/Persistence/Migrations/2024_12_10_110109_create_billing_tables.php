<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla para contratos
        Schema::create('contratos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('paciente_id')->constrained('pacientes');
            $table->foreignUuid('servicio_id')->constrained('servicios');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->enum('estado', ['pendiente', 'activo', 'cancelado', 'finalizado'])->default('pendiente');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['paciente_id', 'estado']); // Para búsquedas de contratos por paciente
        });

        // Tabla para fechas de contrato (Value Object)
        Schema::create('fecha_contratos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->timestamps();
        });

        // Tabla para facturas
        Schema::create('facturas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contrato_id')->constrained('contratos');
            $table->decimal('monto_total', 10, 2);
            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'pagada', 'anulada'])->default('pendiente');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['contrato_id', 'estado']); // Para búsquedas de facturas por contrato
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('fecha_contratos');
        Schema::dropIfExists('contratos');
    }
};
