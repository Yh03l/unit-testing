<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\EventBus;

use Commercial\Domain\Repositories\OutboxRepository;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class RabbitMQEventBus implements EventBus
{
	private AMQPStreamConnection $connection;
	private OutboxRepository $outboxRepository;
	private string $exchange;

	public function __construct(
		AMQPStreamConnection $connection,
		OutboxRepository $outboxRepository,
		string $exchange = 'commercial.events'
	) {
		$this->connection = $connection;
		$this->outboxRepository = $outboxRepository;
		$this->exchange = $exchange;
	}

	public function publish(object $event): void
	{
		$eventType = get_class($event);
		$eventData = $this->serializeEvent($event);

		// Guardar en outbox
		$this->outboxRepository->save($eventType, $eventData);
	}

	public function publishPendingEvents(): void
	{
		$channel = $this->connection->channel();
		$channel->exchange_declare($this->exchange, 'topic', false, true, false);

		$pendingEvents = $this->outboxRepository->findPendingEvents();

		foreach ($pendingEvents as $event) {
			try {
				$message = new AMQPMessage(json_encode($event['event_data']), [
					'content_type' => 'application/json',
					'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
					'message_id' => $event['id'],
					'type' => $event['event_type'],
				]);

				$routingKey = $this->getRoutingKey($event['event_type']);
				$channel->basic_publish($message, $this->exchange, $routingKey);

				$this->outboxRepository->markAsPublished($event['id']);
				Log::info("Evento publicado exitosamente en routing key: $routingKey", [
					'event_id' => $event['id'],
				]);
			} catch (\Exception $e) {
				Log::error('Error al publicar evento', [
					'event_id' => $event['id'],
					'error' => $e->getMessage(),
				]);

				$this->outboxRepository->incrementRetryCount($event['id']);

				if ($event['retry_count'] >= 2) {
					// Después de 3 intentos (0,1,2)
					$this->outboxRepository->markAsFailed($event['id']);
				}
			}
		}

		$channel->close();
	}

	private function serializeEvent(object $event): array
	{
		$data = [];
		$reflection = new \ReflectionClass($event);

		foreach ($reflection->getProperties() as $property) {
			$property->setAccessible(true);
			$value = $property->getValue($event);

			if ($value instanceof \DateTimeImmutable) {
				$value = $value->format('Y-m-d\TH:i:s.u\Z');
			}

			$data[$property->getName()] = $value;
		}

		return $data;
	}

	private function getRoutingKey(string $eventType): string
	{
		$parts = explode('\\', $eventType);
		$parts = array_map(fn($part) => str_replace('_', '.', $part), $parts);

		// Separar camel case en el último segmento (el nombre del evento)
		$last = array_pop($parts);
		// Convierte ContractCreated en contract.created
		$last = preg_replace('/(?<!^)([A-Z])/', '.$1', $last);
		$parts[] = $last;

		// Ahora sí, convierte todo a minúsculas
		$parts = array_map('strtolower', $parts);

		return implode('.', $parts);
	}

	public function __destruct()
	{
		if ($this->connection->isConnected()) {
			$this->connection->close();
		}
	}
}
