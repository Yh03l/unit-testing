<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Commercial\Infrastructure\EventBus\EventBus;

class CheckRabbitMQConnection extends Command
{
	protected $signature = 'commercial:check-rabbitmq';
	protected $description = 'Verificar el estado de la conexión RabbitMQ';

	public function handle(): int
	{
		$this->info('Verificando conexión RabbitMQ...');

		try {
			$connection = app(AMQPStreamConnection::class);

			if ($connection === null) {
				$this->error('❌ No se pudo establecer conexión con RabbitMQ');
				$this->warn('Configuración actual:');
				$this->line('  Host: ' . env('RABBITMQ_HOST', 'localhost'));
				$this->line('  Port: ' . env('RABBITMQ_PORT', 5672));
				$this->line('  User: ' . env('RABBITMQ_USER', 'guest'));
				$this->line('  VHost: ' . env('RABBITMQ_VHOST', '/'));
				return 1;
			}

			if ($connection->isConnected()) {
				$this->info('✅ Conexión RabbitMQ establecida correctamente');
				$connection->close();
				return 0;
			} else {
				$this->error('❌ Conexión RabbitMQ no está activa');
				return 1;
			}
		} catch (\Exception $e) {
			$this->error('❌ Error al conectar con RabbitMQ: ' . $e->getMessage());
			return 1;
		}
	}
}
