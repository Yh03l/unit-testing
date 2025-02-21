<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateCatalog;

use Commercial\Application\Commands\UpdateCatalog\UpdateCatalogCommand;
use Commercial\Application\Commands\UpdateCatalog\UpdateCatalogHandler;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UpdateCatalogHandlerTest extends MockeryTestCase
{
    private UpdateCatalogHandler $handler;
    private CatalogRepository $repository;
    private string $catalogId;
    private string $nombre;
    private ServiceStatus $estado;
    private Catalog $catalog;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(CatalogRepository::class);
        $this->handler = new UpdateCatalogHandler($this->repository);
        $this->catalogId = 'catalog-123';
        $this->nombre = 'Updated Catalog';
        $this->estado = ServiceStatus::ACTIVO;
        $this->catalog = Mockery::mock(Catalog::class);
    }

    public function testHandleUpdatesNombreAndEstadoSuccessfully(): void
    {
        // Arrange
        $command = new UpdateCatalogCommand($this->catalogId, $this->nombre, $this->estado);

        $this->repository->shouldReceive('findById')
            ->with($this->catalogId)
            ->once()
            ->andReturn($this->catalog);

        $this->catalog->shouldReceive('updateNombre')
            ->with($this->nombre)
            ->once();

        $this->catalog->shouldReceive('updateEstado')
            ->with($this->estado)
            ->once();

        $this->repository->shouldReceive('save')
            ->with($this->catalog)
            ->once();

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }

    public function testHandleUpdatesOnlyNombreWhenEstadoIsNull(): void
    {
        // Arrange
        $command = new UpdateCatalogCommand($this->catalogId, $this->nombre, null);

        $this->repository->shouldReceive('findById')
            ->with($this->catalogId)
            ->once()
            ->andReturn($this->catalog);

        $this->catalog->shouldReceive('updateNombre')
            ->with($this->nombre)
            ->once();

        $this->catalog->shouldNotReceive('updateEstado');

        $this->repository->shouldReceive('save')
            ->with($this->catalog)
            ->once();

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }

    public function testHandleThrowsExceptionWhenCatalogNotFound(): void
    {
        // Arrange
        $command = new UpdateCatalogCommand($this->catalogId, $this->nombre, $this->estado);

        $this->repository->shouldReceive('findById')
            ->with($this->catalogId)
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage("Catálogo con ID {$this->catalogId} no encontrado");

        // Act
        $this->handler->handle($command);
    }

    public function testInvokeCallsHandle(): void
    {
        // Arrange
        $command = new UpdateCatalogCommand($this->catalogId, $this->nombre, $this->estado);

        $this->repository->shouldReceive('findById')
            ->with($this->catalogId)
            ->once()
            ->andReturn($this->catalog);

        $this->catalog->shouldReceive('updateNombre')
            ->with($this->nombre)
            ->once();

        $this->catalog->shouldReceive('updateEstado')
            ->with($this->estado)
            ->once();

        $this->repository->shouldReceive('save')
            ->with($this->catalog)
            ->once();

        // Act
        $this->handler->__invoke($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }
} 