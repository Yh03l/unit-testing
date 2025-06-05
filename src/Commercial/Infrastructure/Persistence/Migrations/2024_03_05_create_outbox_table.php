<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('outbox', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('event_type');
			$table->json('event_data');
			$table->string('status')->default('pending'); // pending, published, failed
			$table->integer('retry_count')->default(0);
			$table->timestamp('published_at')->nullable();
			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('outbox');
	}
};
