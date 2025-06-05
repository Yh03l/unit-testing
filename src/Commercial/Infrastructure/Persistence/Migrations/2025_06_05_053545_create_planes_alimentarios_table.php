<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('planes_alimentarios', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('nombre');
			$table->string('tipo');
			$table->integer('cantidad_dias');
			$table->timestamps();

			// Índices para mejor rendimiento
			$table->index('tipo');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('planes_alimentarios');
	}
};
