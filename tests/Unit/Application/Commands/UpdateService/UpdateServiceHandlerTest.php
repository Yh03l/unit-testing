<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateService;

use Commercial\Application\Commands\UpdateService\UpdateServiceCommand;
use Commercial\Application\Commands\UpdateService\UpdateServiceHandler;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UpdateServiceHandlerTest extends MockeryTestCase
{
    private UpdateServiceHandler $handler;
    private ServiceRepository $serviceRepository;
    private CatalogRepository $catalogRepository;
    private UpdateServiceCommand $command;
    private string $serviceId;
    private string $catalogId;
    private Service $service;
    private Catalog $catalog;

    protected function setUp(): void
    {
        $this->serviceRepository = Mockery::mock(ServiceRepository::class);
        $this->catalogRepository = Mockery::mock(CatalogRepository::class);
        $this->handler = new UpdateServiceHandler(
            $this->serviceRepository,
            $this->catalogRepository
        );

        $this->serviceId = 'service-123';
        $this->catalogId = 'catalog-456';
        
        $this->command = new UpdateServiceCommand(
            $this->serviceId,
            'Updated Service Name',
            'Updated Service Description'
        );

        // Mock del servicio
        $this->service = Mockery::mock(Service::class);
        $this->service->shouldReceive('getCatalogoId')
                     ->andReturn($this->catalogId);

        // Mock del catálogo
        $this->catalog = Mockery::mock(Catalog::class);
    }

    public function testHandleUpdatesServiceWhenAllConditionsAreMet(): void
    {
        // Configurar el servicio
        $this->service->shouldReceive('canBeModified')
                     ->once()
                     ->andReturn(true);
        $this->service->shouldReceive('update')
                     ->once()
                     ->with(
                         'Updated Service Name',
                         'Updated Service Description'
                     );

        // Configurar el catálogo
        $this->catalog->shouldReceive('isActive')
                     ->once()
                     ->andReturn(true);

        // Configurar los repositorios
        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn($this->service);
        
        $this->catalogRepository->shouldReceive('findById')
                               ->with($this->catalogId)
                               ->once()
                               ->andReturn($this->catalog);

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

    public function testHandleThrowsExceptionWhenCatalogNotFound(): void
    {
        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn($this->service);

        $this->catalogRepository->shouldReceive('findById')
                               ->with($this->catalogId)
                               ->once()
                               ->andReturn(null);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("El catálogo con ID {$this->catalogId} no está activo");

        $this->handler->handle($this->command);
    }

    public function testHandleThrowsExceptionWhenCatalogNotActive(): void
    {
        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn($this->service);

        $this->catalog->shouldReceive('isActive')
                     ->once()
                     ->andReturn(false);

        $this->catalogRepository->shouldReceive('findById')
                               ->with($this->catalogId)
                               ->once()
                               ->andReturn($this->catalog);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("El catálogo con ID {$this->catalogId} no está activo");

        $this->handler->handle($this->command);
    }

    public function testHandleThrowsExceptionWhenServiceCannotBeModified(): void
    {
        $this->serviceRepository->shouldReceive('findById')
                               ->with($this->serviceId)
                               ->once()
                               ->andReturn($this->service);

        $this->catalog->shouldReceive('isActive')
                     ->once()
                     ->andReturn(true);

        $this->catalogRepository->shouldReceive('findById')
                               ->with($this->catalogId)
                               ->once()
                               ->andReturn($this->catalog);

        $this->service->shouldReceive('canBeModified')
                     ->once()
                     ->andReturn(false);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("El servicio con ID {$this->serviceId} no puede ser modificado en su estado actual");

        $this->handler->handle($this->command);
    }
} 