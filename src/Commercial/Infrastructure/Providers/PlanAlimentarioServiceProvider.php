<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Providers;

use Commercial\Domain\Repositories\PlanAlimentarioRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentPlanAlimentarioRepository;
use Commercial\Infrastructure\Persistence\Eloquent\PlanAlimentarioModel;
use Commercial\Application\Commands\CreatePlanAlimentario\CreatePlanAlimentarioCommandHandler;
use Illuminate\Support\ServiceProvider;

final class PlanAlimentarioServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		// Registrar el modelo
		$this->app->bind(PlanAlimentarioModel::class, function ($app) {
			return new PlanAlimentarioModel();
		});

		// Registrar el repositorio
		$this->app->bind(
			PlanAlimentarioRepository::class,
			EloquentPlanAlimentarioRepository::class
		);

		// Registrar el handler
		$this->app->bind(CreatePlanAlimentarioCommandHandler::class, function ($app) {
			return new CreatePlanAlimentarioCommandHandler(
				$app->make(PlanAlimentarioRepository::class)
			);
		});
	}

	public function boot(): void
	{
		// Registrar las migraciones
		$this->loadMigrationsFrom([__DIR__ . '/../Persistence/Migrations']);
	}
}
