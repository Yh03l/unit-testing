<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\MessageBroker\RabbitMQ;

use Commercial\Application\Commands\CreatePlanAlimentario\CreatePlanAlimentarioCommand;
use Commercial\Application\Commands\CreatePlanAlimentario\CreatePlanAlimentarioCommandHandler;
use Commercial\Application\DTOs\PlanAlimentarioCreadoDTO;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

final class PlanAlimentarioCreatedConsumer
{
	public function __construct(private CreatePlanAlimentarioCommandHandler $handler) {}

	public function __invoke(AMQPMessage $message): void
	{
		try {
			$data = json_decode($message->body, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \RuntimeException('Error decodificando JSON: ' . json_last_error_msg());
			}

			$dto = PlanAlimentarioCreadoDTO::fromArray($data);

			$command = new CreatePlanAlimentarioCommand(
				$dto->idPlanAlimentario,
				$dto->nombre,
				$dto->tipo,
				$dto->cantidadDias
			);

			$this->handler->handle($command);

			Log::info('Plan alimentario creado exitosamente', [
				'id' => $dto->idPlanAlimentario,
				'nombre' => $dto->nombre,
				'tipo' => $dto->tipo,
				'dias' => $dto->cantidadDias,
			]);
		} catch (\Exception $e) {
			Log::error('Error al procesar plan alimentario', [
				'error' => $e->getMessage(),
				'payload' => $message->body,
			]);
			throw $e;
		}
	}
}
