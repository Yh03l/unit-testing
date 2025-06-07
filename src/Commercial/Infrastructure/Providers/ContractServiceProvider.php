<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Providers;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentContractRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentServiceRepository;
use Commercial\Infrastructure\Persistence\Eloquent\ContractModel;
use Commercial\Infrastructure\EventBus\RabbitMQEventBus;
use Commercial\Application\Commands\AssignPlanAlimentario\AssignPlanAlimentarioHandler;
use Commercial\Application\Commands\CreateContract\CreateContractHandler;
use Illuminate\Support\ServiceProvider;

final class ContractServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		// Registrar el modelo
		$this->app->bind(ContractModel::class, function ($app) {
			return new ContractModel();
		});

		// Registrar los repositorios
		$this->app->bind(ContractRepository::class, function ($app) {
			return new EloquentContractRepository(
				$app->make(ContractModel::class),
				$app->make(ServiceRepository::class)
			);
		});

		// Registrar el handler
		$this->app->bind(AssignPlanAlimentarioHandler::class, function ($app) {
			return new AssignPlanAlimentarioHandler(
				$app->make(ContractRepository::class),
				$app->make(ServiceRepository::class),
				$app->make(RabbitMQEventBus::class)
			);
		});

		// Registrar el CreateContractHandler
		$this->app->bind(CreateContractHandler::class, function ($app) {
			return new CreateContractHandler(
				$app->make(ContractRepository::class),
				$app->make(ServiceRepository::class),
				$app->make(RabbitMQEventBus::class)
			);
		});
	}

	public function boot(): void
	{
		// Registrar las migraciones
		$this->loadMigrationsFrom([__DIR__ . '/../Persistence/Migrations']);
	}
}
