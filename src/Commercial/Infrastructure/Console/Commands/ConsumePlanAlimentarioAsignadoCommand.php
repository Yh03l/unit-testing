<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Commercial\Infrastructure\MessageBroker\RabbitMQ\PlanAlimentarioAsignadoConsumer;
use Commercial\Application\Commands\AssignPlanAlimentario\AssignPlanAlimentarioHandler;
use Illuminate\Support\Facades\Log;

class ConsumePlanAlimentarioAsignadoCommand extends Command
{
	protected $signature = 'commercial:consume-plan-alimentario-asignado';
	protected $description = 'Consume eventos de plan alimentario asignado';

	private AMQPStreamConnection $connection;
	private AssignPlanAlimentarioHandler $handler;

	public function __construct(
		AMQPStreamConnection $connection,
		AssignPlanAlimentarioHandler $handler
	) {
		parent::__construct();
		$this->connection = $connection;
		$this->handler = $handler;
	}

	public function handle(): int
	{
		$this->info('Iniciando consumidor de eventos de plan alimentario asignado...');

		try {
			$channel = $this->connection->channel();
			$queue = 'comercial.plan-alimentario-asignado';

			// Declarar la cola
			$channel->queue_declare($queue, false, true, false, false);

			$consumer = new PlanAlimentarioAsignadoConsumer($this->handler);

			$callback = function ($msg) use ($consumer) {
				try {
					$this->info('Mensaje recibido: ' . $msg->body);
					$consumer($msg);
					$this->info('Mensaje procesado correctamente');
				} catch (\Exception $e) {
					$this->error('Error procesando el mensaje: ' . $e->getMessage());
					Log::error('Error procesando mensaje de plan alimentario asignado', [
						'error' => $e->getMessage(),
						'payload' => $msg->body,
					]);
				}
			};

			$channel->basic_consume($queue, '', false, true, false, false, $callback);

			$this->info(" [*] Esperando mensajes en la cola: {$queue}. Para salir presiona CTRL+C");

			while ($this->connection->isConnected()) {
				try {
					$channel->wait();
				} catch (\ErrorException $e) {
					$this->error('Error de conexión: ' . $e->getMessage());
					break;
				}
			}

			return self::SUCCESS;
		} catch (\Exception $e) {
			$this->error('Error iniciando el consumidor: ' . $e->getMessage());
			return self::FAILURE;
		}
	}
}
