<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\Repositories\OutboxRepository;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentCatalogRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentServiceRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentContractRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentOutboxRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Infrastructure\Bus\LaravelCommandBus;
use Commercial\Infrastructure\Bus\LaravelQueryBus;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Infrastructure\EventBus\RabbitMQEventBus;
use Commercial\Infrastructure\Console\Commands\PublishOutboxEvents;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Commercial\Infrastructure\EventBus\InMemoryEventBus;

class CommercialServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		// Registrar repositorios
		$this->app->bind(CatalogRepository::class, EloquentCatalogRepository::class);

		$this->app->bind(ServiceRepository::class, EloquentServiceRepository::class);

		$this->app->bind(ContractRepository::class, EloquentContractRepository::class);

		$this->app->bind(OutboxRepository::class, EloquentOutboxRepository::class);

		$this->app->bind(UserRepository::class, EloquentUserRepository::class);

		// Registrar buses
		$this->app->bind(LaravelCommandBus::class, function ($app) {
			return new LaravelCommandBus($app);
		});

		$this->app->bind(LaravelQueryBus::class, function ($app) {
			return new LaravelQueryBus($app);
		});

		$this->app->bind(CommandBus::class, LaravelCommandBus::class);
		$this->app->bind(QueryBus::class, LaravelQueryBus::class);

		// Registrar RabbitMQ
		$this->app->singleton(AMQPStreamConnection::class, function ($app) {
			if ($app->environment('testing')) {
				return null;
			}

			static $connection = null;
			if ($connection !== null) {
				return $connection;
			}

			try {
				$connection = new AMQPStreamConnection(
					env('RABBITMQ_HOST', 'localhost'),
					(int) env('RABBITMQ_PORT', 5672),
					env('RABBITMQ_USER', 'guest'),
					env('RABBITMQ_PASSWORD', 'guest'),
					env('RABBITMQ_VHOST', '/'),
					false, // lazy connection
					'AMQPLAIN',
					null,
					'en_US',
					3.0, // connection timeout
					3.0, // read timeout
					null, // write timeout
					false, // keepalive
					0 // heartbeat
				);
				Log::info('RabbitMQ connection established successfully');
				return $connection;
			} catch (\Exception $e) {
				Log::warning('RabbitMQ connection failed: ' . $e->getMessage());
				return null;
			}
		});

		// Registrar EventBus
		$this->app->singleton(EventBus::class, function ($app) {
			static $eventBus = null;
			if ($eventBus !== null) {
				return $eventBus;
			}

			if ($app->environment('testing')) {
				$eventBus = new InMemoryEventBus();
				return $eventBus;
			}

			try {
				$connection = $app->make(AMQPStreamConnection::class);
				if ($connection !== null) {
					$eventBus = new RabbitMQEventBus(
						$connection,
						$app->make(OutboxRepository::class)
					);
					Log::info('Using RabbitMQEventBus for event publishing');
					return $eventBus;
				}
			} catch (\Exception $e) {
				Log::warning('Failed to initialize RabbitMQEventBus: ' . $e->getMessage());
			}

			// Fallback a InMemoryEventBus si RabbitMQ no está disponible
			$eventBus = new InMemoryEventBus();
			Log::warning('Falling back to InMemoryEventBus - events will be stored in memory only');
			return $eventBus;
		});
	}

	public function boot(): void
	{
		// Registrar las migraciones
		$this->loadMigrationsFrom([__DIR__ . '/../Persistence/Migrations']);

		// Registrar comandos
		if ($this->app->runningInConsole()) {
			$this->commands([PublishOutboxEvents::class]);

			$this->publishes(
				[
					__DIR__ . '/../Persistence/Migrations' => $this->getDatabasePath('migrations'),
				],
				'commercial-migrations'
			);
		}
	}

	protected function getDatabasePath(string $path = ''): string
	{
		return database_path($path);
	}
}
