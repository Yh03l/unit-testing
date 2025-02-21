<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateServiceCost;

use Commercial\Application\Commands\UpdateServiceCost\UpdateServiceCostCommand;
use Commercial\Application\Commands\UpdateServiceCost\UpdateServiceCostHandler;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceCost;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UpdateServiceCostHandlerTest extends MockeryTestCase
{
    private UpdateServiceCostHandler $handler;
    private ServiceRepository $serviceRepository;
    private UpdateServiceCostCommand $command;
    private string $serviceId;
    private Service $service;
    private ServiceCost $newCost;

    protected function setUp(): void
    {
        $this->serviceRepository = Mockery::mock(ServiceRepository::class);
        $this->handler = new UpdateServiceCostHandler($this->serviceRepository);

        $this->serviceId = 'service-123';
        $this->command = new UpdateServiceCostCommand(
            $this->serviceId,
            150.00,
            'BOB',
            new DateTimeImmutable('2024-12-31')
        );

        $this->newCost = new ServiceCost(
            150.00,
            'BOB',
            new DateTimeImmutable('2024-12-31')
        );

        $this->service = Mockery::mock(Service::class);
    }

    public function testHandleUpdatesCostWhenServiceExists(): void
    {
        $this->service->shouldReceive('canUpdateCost')
                     ->once()
                     ->andReturn(true);

        $this->service->shouldReceive('updateCost')
                     ->once()
                     ->with(Mockery::type(ServiceCost::class));

        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn($this->service);

        $this->serviceRepository->shouldReceive('save')
                               ->once()
                               ->with($this->service);

        $this->handler->handle($this->command);
        $this->assertTrue(true); // Verifica que no se lanzaron excepciones
    }

    public function testHandleThrowsExceptionWhenServiceNotFound(): void
    {
        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn(null);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("No se encontró el servicio con ID {$this->serviceId}");

        $this->handler->handle($this->command);
    }

    public function testHandleThrowsExceptionWhenServiceCannotUpdateCost(): void
    {
        $this->service->shouldReceive('canUpdateCost')
                     ->once()
                     ->andReturn(false);

        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn($this->service);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("No se puede actualizar el costo del servicio con ID {$this->serviceId}");

        $this->handler->handle($this->command);
    }
} 