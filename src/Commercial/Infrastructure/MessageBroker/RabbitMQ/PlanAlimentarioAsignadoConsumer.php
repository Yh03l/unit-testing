<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\MessageBroker\RabbitMQ;

use Commercial\Application\Commands\AssignPlanAlimentario\AssignPlanAlimentarioCommand;
use Commercial\Application\Commands\AssignPlanAlimentario\AssignPlanAlimentarioHandler;
use Commercial\Application\DTOs\PlanAlimentarioAsignadoDTO;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

final class PlanAlimentarioAsignadoConsumer
{
	public function __construct(private AssignPlanAlimentarioHandler $handler) {}

	public function __invoke(AMQPMessage $message): void
	{
		try {
			$data = json_decode($message->body, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \RuntimeException('Error decodificando JSON: ' . json_last_error_msg());
			}

			$dto = PlanAlimentarioAsignadoDTO::fromArray($data);

			$command = new AssignPlanAlimentarioCommand($dto->idContrato, $dto->idPlanAlimentario);

			$this->handler->handle($command);

			Log::info('Plan alimentario asignado exitosamente', [
				'idContrato' => $dto->idContrato,
				'idPlanAlimentario' => $dto->idPlanAlimentario,
			]);
		} catch (\Exception $e) {
			Log::error('Error al procesar asignación de plan alimentario', [
				'error' => $e->getMessage(),
				'payload' => $message->body,
			]);
			throw $e;
		}
	}
}
