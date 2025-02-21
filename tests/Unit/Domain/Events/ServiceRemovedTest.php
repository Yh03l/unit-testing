<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events;

use Commercial\Domain\Events\ServiceRemoved;
use PHPUnit\Framework\TestCase;

class ServiceRemovedTest extends TestCase
{
    private string $catalogId;
    private string $serviceId;
    private ServiceRemoved $event;

    protected function setUp(): void
    {
        $this->catalogId = 'catalog-123';
        $this->serviceId = 'service-456';
        $this->event = new ServiceRemoved($this->catalogId, $this->serviceId);
    }

    public function testGetCatalogId(): void
    {
        $this->assertEquals($this->catalogId, $this->event->getCatalogId());
    }

    public function testGetServiceId(): void
    {
        $this->assertEquals($this->serviceId, $this->event->getServiceId());
    }

    public function testGetOccurredOn(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }
} 