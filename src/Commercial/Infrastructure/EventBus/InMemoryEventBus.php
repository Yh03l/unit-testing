<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\EventBus;

class InMemoryEventBus implements EventBus
{
	private array $events = [];

	public function publish(object $event): void
	{
		$this->events[] = $event;
	}

	public function publishPendingEvents(): void
	{
		// En el bus en memoria, los eventos ya están "publicados" cuando se llama a publish
		// Este método es una no-op para mantener la compatibilidad con la interfaz
	}

	public function getEvents(): array
	{
		return $this->events;
	}

	public function clear(): void
	{
		$this->events = [];
	}
}
