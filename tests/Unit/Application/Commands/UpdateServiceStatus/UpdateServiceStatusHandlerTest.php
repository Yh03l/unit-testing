<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateServiceStatus;

use Commercial\Application\Commands\UpdateServiceStatus\UpdateServiceStatusCommand;
use Commercial\Application\Commands\UpdateServiceStatus\UpdateServiceStatusHandler;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UpdateServiceStatusHandlerTest extends MockeryTestCase
{
    private UpdateServiceStatusHandler $handler;
    private ServiceRepository $serviceRepository;
    private UpdateServiceStatusCommand $command;
    private string $serviceId;
    private Service $service;

    protected function setUp(): void
    {
        $this->serviceRepository = Mockery::mock(ServiceRepository::class);
        $this->handler = new UpdateServiceStatusHandler($this->serviceRepository);

        $this->serviceId = 'service-123';
        $this->command = new UpdateServiceStatusCommand(
            $this->serviceId,
            ServiceStatus::INACTIVO
        );

        // Mock del servicio
        $this->service = Mockery::mock(Service::class);
    }

    public function testHandleUpdatesServiceStatusWhenServiceExists(): void
    {
        $this->service->shouldReceive('updateEstado')
                     ->once()
                     ->with(ServiceStatus::INACTIVO);

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

    public function testHandleThrowsExceptionWhenStatusTransitionIsInvalid(): void
    {
        $this->service->shouldReceive('updateEstado')
                     ->once()
                     ->with(ServiceStatus::INACTIVO)
                     ->andThrow(CatalogException::invalidStatusTransition(
                         ServiceStatus::SUSPENDIDO,
                         ServiceStatus::INACTIVO
                     ));

        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn($this->service);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage(
            "No se puede cambiar el estado del servicio de suspendido a inactivo"
        );

        $this->handler->handle($this->command);
    }
} 