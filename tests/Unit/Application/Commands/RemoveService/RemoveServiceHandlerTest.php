<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\RemoveService;

use Commercial\Application\Commands\RemoveService\RemoveServiceCommand;
use Commercial\Application\Commands\RemoveService\RemoveServiceHandler;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Exceptions\CatalogException;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RemoveServiceHandlerTest extends MockeryTestCase
{
    private RemoveServiceHandler $handler;
    private CatalogRepository $repository;
    private RemoveServiceCommand $command;
    private string $catalogId;
    private string $serviceId;
    private Catalog $catalog;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(CatalogRepository::class);
        $this->handler = new RemoveServiceHandler($this->repository);

        $this->catalogId = 'catalog-123';
        $this->serviceId = 'service-456';
        $this->command = new RemoveServiceCommand(
            $this->catalogId,
            $this->serviceId
        );

        $this->catalog = Mockery::mock(Catalog::class);
    }

    public function testHandleRemovesServiceWhenCatalogExists(): void
    {
        $this->repository->shouldReceive('findById')
                        ->with($this->catalogId)
                        ->once()
                        ->andReturn($this->catalog);

        $this->catalog->shouldReceive('removeService')
                     ->with($this->serviceId)
                     ->once();

        $this->repository->shouldReceive('save')
                        ->with($this->catalog)
                        ->once();

        $this->handler->handle($this->command);
        $this->assertTrue(true); // Verifica que no se lanzaron excepciones
    }

    public function testHandleThrowsExceptionWhenCatalogNotFound(): void
    {
        $this->repository->shouldReceive('findById')
                        ->with($this->catalogId)
                        ->once()
                        ->andReturn(null);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("Catálogo con ID {$this->catalogId} no encontrado");

        $this->handler->handle($this->command);
    }

    public function testHandleThrowsExceptionWhenServiceNotFound(): void
    {
        $this->repository->shouldReceive('findById')
                        ->with($this->catalogId)
                        ->once()
                        ->andReturn($this->catalog);

        $this->catalog->shouldReceive('removeService')
                     ->with($this->serviceId)
                     ->once()
                     ->andThrow(CatalogException::serviceNotFound($this->serviceId));

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("No se encontró el servicio con ID {$this->serviceId}");

        $this->handler->handle($this->command);
    }
} 