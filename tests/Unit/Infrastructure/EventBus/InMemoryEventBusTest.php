<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\EventBus;

use Commercial\Infrastructure\EventBus\InMemoryEventBus;
use PHPUnit\Framework\TestCase;

class InMemoryEventBusTest extends TestCase
{
    private InMemoryEventBus $eventBus;

    protected function setUp(): void
    {
        $this->eventBus = new InMemoryEventBus();
    }

    public function testPublishAddsEventToCollection(): void
    {
        $event = new \stdClass();
        $event->name = 'TestEvent';

        $this->eventBus->publish($event);

        $events = $this->eventBus->getEvents();
        $this->assertCount(1, $events);
        $this->assertSame($event, $events[0]);
    }

    public function testGetEventsReturnsAllEvents(): void
    {
        $event1 = new \stdClass();
        $event1->name = 'TestEvent1';

        $event2 = new \stdClass();
        $event2->name = 'TestEvent2';

        $this->eventBus->publish($event1);
        $this->eventBus->publish($event2);

        $events = $this->eventBus->getEvents();
        $this->assertCount(2, $events);
        $this->assertSame($event1, $events[0]);
        $this->assertSame($event2, $events[1]);
    }

    public function testClearEventsRemovesAllEvents(): void
    {
        $event = new \stdClass();
        $event->name = 'TestEvent';

        $this->eventBus->publish($event);
        $this->assertCount(1, $this->eventBus->getEvents());

        $this->eventBus->clearEvents();
        $this->assertCount(0, $this->eventBus->getEvents());
    }

    public function testEventsAreStoredInOrder(): void
    {
        $events = [];
        for ($i = 0; $i < 5; $i++) {
            $event = new \stdClass();
            $event->name = "TestEvent{$i}";
            $events[] = $event;
            $this->eventBus->publish($event);
        }

        $storedEvents = $this->eventBus->getEvents();
        $this->assertCount(5, $storedEvents);
        
        foreach ($events as $index => $event) {
            $this->assertSame($event, $storedEvents[$index]);
        }
    }
} 