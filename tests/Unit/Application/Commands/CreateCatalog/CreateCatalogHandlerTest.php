<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CreateCatalog;

use Commercial\Application\Commands\CreateCatalog\CreateCatalogCommand;
use Commercial\Application\Commands\CreateCatalog\CreateCatalogHandler;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Domain\Events\CatalogCreated;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CreateCatalogHandlerTest extends MockeryTestCase
{
    private CreateCatalogHandler $handler;
    private CatalogRepository $repository;
    private EventBus $eventBus;
    private string $nombre;
    private ServiceStatus $estado;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(CatalogRepository::class);
        $this->eventBus = Mockery::mock(EventBus::class);
        $this->handler = new CreateCatalogHandler($this->repository, $this->eventBus);
        $this->nombre = 'Test Catalog';
        $this->estado = ServiceStatus::ACTIVO;
    }

    public function testHandleCreatesAndSavesCatalogSuccessfully(): void
    {
        // Arrange
        $command = new CreateCatalogCommand($this->nombre, $this->estado);

        $this->repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Catalog::class))
            ->andReturnUsing(function (Catalog $catalog) {
                $catalog->addEvent(new CatalogCreated($catalog->getId(), $catalog->getNombre(), $catalog->getEstado()));
                return $catalog;
            });

        $this->eventBus->shouldReceive('publish')
            ->once()
            ->with(Mockery::type(CatalogCreated::class));

        // Act
        $this->handler->__invoke($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }

    public function testHandlePublishesMultipleEvents(): void
    {
        // Arrange
        $command = new CreateCatalogCommand($this->nombre, $this->estado);

        $this->repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Catalog::class))
            ->andReturnUsing(function (Catalog $catalog) {
                $catalog->addEvent(new CatalogCreated($catalog->getId(), $catalog->getNombre(), $catalog->getEstado()));
                $catalog->addEvent(new CatalogCreated($catalog->getId(), $catalog->getNombre(), $catalog->getEstado()));
                return $catalog;
            });

        $this->eventBus->shouldReceive('publish')
            ->twice()
            ->with(Mockery::type(CatalogCreated::class));

        // Act
        $this->handler->__invoke($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }
}