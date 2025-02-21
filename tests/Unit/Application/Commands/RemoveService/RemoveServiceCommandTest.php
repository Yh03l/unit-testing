<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\RemoveService;

use Commercial\Application\Commands\RemoveService\RemoveServiceCommand;
use PHPUnit\Framework\TestCase;

class RemoveServiceCommandTest extends TestCase
{
    private RemoveServiceCommand $command;
    private string $catalogId;
    private string $serviceId;

    protected function setUp(): void
    {
        $this->catalogId = 'catalog-123';
        $this->serviceId = 'service-456';

        $this->command = new RemoveServiceCommand(
            $this->catalogId,
            $this->serviceId
        );
    }

    public function testGetCatalogId(): void
    {
        $this->assertEquals($this->catalogId, $this->command->getCatalogId());
    }

    public function testGetServiceId(): void
    {
        $this->assertEquals($this->serviceId, $this->command->getServiceId());
    }
} 