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
			// Limpiar el mensaje de posibles espacios y saltos de línea
			$cleanJson = trim(preg_replace('/\s+/', '', $message->body));

			// Reemplazar comillas escapadas por comillas normales
			$cleanJson = str_replace('\"', '"', $cleanJson);

			// Si el JSON está envuelto en comillas dobles, removerlas
			if (str_starts_with($cleanJson, '"') && str_ends_with($cleanJson, '"')) {
				$cleanJson = substr($cleanJson, 1, -1);
			}

			$data = json_decode($cleanJson, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \RuntimeException(
					'Error decodificando JSON: ' .
						json_last_error_msg() .
						'. JSON recibido: ' .
						$cleanJson
				);
			}

			// Normalizar claves del JSON para manejar variaciones de mayúsculas
			$data = $this->normalizeJsonKeys($data);

			// Validar campos requeridos
			if (!isset($data['idContrato']) || !isset($data['idPlanAlimentario'])) {
				throw new \RuntimeException(
					'JSON inválido: faltan campos requeridos. Se requiere idContrato e idPlanAlimentario'
				);
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

	/**
	 * Normaliza las claves del JSON para manejar variaciones de mayúsculas
	 */
	private function normalizeJsonKeys(array $data): array
	{
		// Buscar si existe IdContrato y asignarlo a idContrato
		if (isset($data['IdContrato']) && !isset($data['idContrato'])) {
			$data['idContrato'] = $data['IdContrato'];
		}

		// Buscar si existe IdPlan y asignarlo a idPlanAlimentario
		if (isset($data['IdPlanAlimentario']) && !isset($data['idPlanAlimentario'])) {
			$data['idPlanAlimentario'] = $data['IdPlanAlimentario'];
		}

		return $data;
	}
}
