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

    public function getEvents(): array
    {
        return $this->events;
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }
} 