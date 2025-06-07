<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\EventBus;

use Commercial\Domain\Events\DomainEvent;
use Commercial\Domain\Repositories\OutboxRepository;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class RabbitMQEventBus implements EventBus
{
	private AMQPStreamConnection $connection;
	private OutboxRepository $outboxRepository;

	public function __construct(
		AMQPStreamConnection $connection,
		OutboxRepository $outboxRepository
	) {
		$this->connection = $connection;
		$this->outboxRepository = $outboxRepository;
	}

	public function publish(object $event): void
	{
		if (!$event instanceof DomainEvent) {
			throw new \InvalidArgumentException('El evento debe implementar DomainEvent');
		}

		$eventType = get_class($event);
		$eventData = $this->serializeEvent($event);
		$eventData['exchange'] = $event->getExchangeName(); // Guardamos el exchange en los datos

		// Guardar en outbox
		$this->outboxRepository->save($eventType, $eventData);
	}

	public function publishPendingEvents(): void
	{
		$channel = $this->connection->channel();
		$pendingEvents = $this->outboxRepository->findPendingEvents();

		foreach ($pendingEvents as $event) {
			try {
				$exchange =
					$event['event_data']['exchange'] ??
					throw new \RuntimeException('Exchange no definido en el evento');
				unset($event['event_data']['exchange']); // Removemos el exchange de los datos antes de publicar

				// Declaramos el exchange para cada evento
				$channel->exchange_declare($exchange, 'fanout', false, true, false);

				$message = new AMQPMessage(json_encode($event['event_data']), [
					'content_type' => 'application/json',
					'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
					'message_id' => $event['id'],
					'type' => $event['event_type'],
				]);

				// En fanout el routing key se ignora, así que usamos string vacío
				$channel->basic_publish($message, $exchange, '');

				$this->outboxRepository->markAsPublished($event['id']);
				Log::info('Evento publicado exitosamente', [
					'event_id' => $event['id'],
					'event_type' => $event['event_type'],
					'exchange' => $exchange,
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

	public function __destruct()
	{
		if ($this->connection->isConnected()) {
			$this->connection->close();
		}
	}
}
