<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListCatalogs;

use Commercial\Application\Queries\ListCatalogs\ListCatalogsQuery;
use Commercial\Application\Queries\ListCatalogs\ListCatalogsHandler;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Application\DTOs\CatalogDTO;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ListCatalogsHandlerTest extends MockeryTestCase
{
    private ListCatalogsHandler $handler;
    private CatalogRepository $repository;
    private Catalog $activeCatalog;
    private Catalog $inactiveCatalog;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = Mockery::mock(CatalogRepository::class);
        $this->handler = new ListCatalogsHandler($this->repository);
        
        $activeStatus = ServiceStatus::ACTIVO;
        $inactiveStatus = ServiceStatus::INACTIVO;
        
        $this->activeCatalog = Mockery::mock(Catalog::class);
        $this->activeCatalog->shouldReceive('getId')->andReturn('1');
        $this->activeCatalog->shouldReceive('getNombre')->andReturn('Catálogo Activo');
        $this->activeCatalog->shouldReceive('getEstado')->andReturn($activeStatus);
        $this->activeCatalog->shouldReceive('getServices')->andReturn([]);
        
        $this->inactiveCatalog = Mockery::mock(Catalog::class);
        $this->inactiveCatalog->shouldReceive('getId')->andReturn('2');
        $this->inactiveCatalog->shouldReceive('getNombre')->andReturn('Catálogo Inactivo');
        $this->inactiveCatalog->shouldReceive('getEstado')->andReturn($inactiveStatus);
        $this->inactiveCatalog->shouldReceive('getServices')->andReturn([]);
    }

    public function testHandleReturnsAllCatalogsWhenNoEstado(): void
    {
        $query = new ListCatalogsQuery();
        $allCatalogs = [$this->activeCatalog, $this->inactiveCatalog];
        
        $this->repository->shouldReceive('findAll')
            ->once()
            ->andReturn($allCatalogs);

        $result = $this->handler->handle($query);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(CatalogDTO::class, $result);
    }

    public function testHandleReturnsFilteredCatalogsByEstado(): void
    {
        $query = new ListCatalogsQuery(ServiceStatus::ACTIVO->value);
        $allCatalogs = [$this->activeCatalog, $this->inactiveCatalog];
        
        $this->repository->shouldReceive('findAll')
            ->once()
            ->andReturn($allCatalogs);

        $result = $this->handler->handle($query);

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(CatalogDTO::class, $result);
        $this->assertEquals(ServiceStatus::ACTIVO->value, $result[0]->estado->value);
    }

    public function testInvokeCallsHandle(): void
    {
        $query = new ListCatalogsQuery();
        $allCatalogs = [$this->activeCatalog, $this->inactiveCatalog];
        
        $this->repository->shouldReceive('findAll')
            ->once()
            ->andReturn($allCatalogs);

        $result = ($this->handler)($query);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(CatalogDTO::class, $result);
    }
} 