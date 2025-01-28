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
        // Tabla para catálogos
        Schema::create('catalogos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre')->index();
            $table->string('estado')->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla para servicios
        Schema::create('servicios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre')->index();
            $table->text('descripcion');
            $table->decimal('costo', 10, 2)->nullable();
            $table->enum('tipo_servicio_id', ['asesoramiento', 'catering']);
            $table->enum('estado', ['activo', 'inactivo', 'descontinuado'])->default('activo');
            $table->foreignUuid('catalogo_id')->constrained('catalogos')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla para costos de servicios (Value Object)
        Schema::create('costo_servicios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->string('moneda')->default('MXN');
            $table->date('vigencia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costo_servicios');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('catalogos');
    }
};
