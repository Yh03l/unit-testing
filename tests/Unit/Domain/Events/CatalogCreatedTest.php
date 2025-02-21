<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events;

use Commercial\Domain\Events\CatalogCreated;
use PHPUnit\Framework\TestCase;

class CatalogCreatedTest extends TestCase
{
    private string $catalogId;
    private CatalogCreated $event;

    protected function setUp(): void
    {
        $this->catalogId = 'catalog-123';
        $this->event = new CatalogCreated($this->catalogId);
    }

    public function testGetCatalogId(): void
    {
        $this->assertEquals($this->catalogId, $this->event->getCatalogId());
    }

    public function testGetOccurredOn(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }
} 