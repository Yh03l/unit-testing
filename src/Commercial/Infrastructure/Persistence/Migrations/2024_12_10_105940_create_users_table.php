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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            
            $table->string('apellido')->after('nombre');
            $table->enum('tipo_usuario', ['admin', 'paciente'])->comment('admin o paciente');
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->softDeletes();
        });

        // Tabla para administradores
        Schema::create('administradores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('cargo');
            $table->json('permisos');
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla para pacientes
        Schema::create('pacientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->date('fecha_nacimiento');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
        Schema::dropIfExists('administradores');
        Schema::dropIfExists('users');
    }
};
