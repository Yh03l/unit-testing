<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\DeleteCatalog;

use Commercial\Application\Commands\DeleteCatalog\DeleteCatalogCommand;
use Commercial\Application\Commands\DeleteCatalog\DeleteCatalogHandler;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Exceptions\CatalogException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DeleteCatalogHandlerTest extends MockeryTestCase
{
    private DeleteCatalogHandler $handler;
    private CatalogRepository $repository;
    private string $catalogId;
    private Catalog $catalog;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(CatalogRepository::class);
        $this->handler = new DeleteCatalogHandler($this->repository);
        $this->catalogId = 'catalog-123';
        $this->catalog = Mockery::mock(Catalog::class);
    }

    public function testHandleDeletesCatalogSuccessfully(): void
    {
        // Arrange
        $command = new DeleteCatalogCommand($this->catalogId);

        $this->repository->shouldReceive('findById')
            ->with($this->catalogId)
            ->once()
            ->andReturn($this->catalog);

        $this->repository->shouldReceive('delete')
            ->with($this->catalogId)
            ->once();

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }

    public function testHandleThrowsExceptionWhenCatalogNotFound(): void
    {
        // Arrange
        $command = new DeleteCatalogCommand($this->catalogId);

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
        $command = new DeleteCatalogCommand($this->catalogId);

        $this->repository->shouldReceive('findById')
            ->with($this->catalogId)
            ->once()
            ->andReturn($this->catalog);

        $this->repository->shouldReceive('delete')
            ->with($this->catalogId)
            ->once();

        // Act
        $this->handler->__invoke($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }
} 