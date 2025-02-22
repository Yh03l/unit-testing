<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Infrastructure\Persistence\Eloquent\EloquentCatalogRepository;
use Commercial\Infrastructure\Persistence\Eloquent\CatalogModel;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Illuminate\Database\Eloquent\Collection;

class EloquentCatalogRepositoryTest extends MockeryTestCase
{
    private EloquentCatalogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentCatalogRepository();
    }

    public function testSaveCreatesCatalogModel(): void
    {
        $catalog = Catalog::create(
            'test-id',
            'Test Catalog',
            ServiceStatus::ACTIVO
        );

        // Mock de la clase CatalogModel usando Mockery
        $modelMock = Mockery::mock('alias:' . CatalogModel::class);
        $modelMock->shouldReceive('updateOrCreate')
            ->once()
            ->with(
                ['id' => $catalog->getId()],
                [
                    'nombre' => $catalog->getNombre(),
                    'estado' => $catalog->getEstado()->toString()
                ]
            );

        $this->repository->save($catalog);
    }

    public function testFindByIdReturnsCatalogWhenExists(): void
    {
        $id = 'test-id';
        $modelMock = Mockery::mock('alias:' . CatalogModel::class);
        
        $catalogModel = new CatalogModel();
        $catalogModel->id = $id;
        $catalogModel->nombre = 'Test Catalog';
        $catalogModel->estado = ServiceStatus::ACTIVO->toString();

        $modelMock->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($catalogModel);

        $result = $this->repository->findById($id);

        $this->assertInstanceOf(Catalog::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals('Test Catalog', $result->getNombre());
        $this->assertEquals(ServiceStatus::ACTIVO, $result->getEstado());
    }

    public function testFindByIdReturnsNullWhenNotExists(): void
    {
        $id = 'non-existent-id';
        $modelMock = Mockery::mock('alias:' . CatalogModel::class);
        
        $modelMock->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(null);

        $result = $this->repository->findById($id);

        $this->assertNull($result);
    }

    public function testFindAllReturnsAllCatalogs(): void
    {
        $modelMock = Mockery::mock('alias:' . CatalogModel::class);
        
        $catalog1 = new CatalogModel();
        $catalog1->id = 'test-id-1';
        $catalog1->nombre = 'Test Catalog 1';
        $catalog1->estado = ServiceStatus::ACTIVO->toString();

        $catalog2 = new CatalogModel();
        $catalog2->id = 'test-id-2';
        $catalog2->nombre = 'Test Catalog 2';
        $catalog2->estado = ServiceStatus::INACTIVO->toString();

        $collection = new Collection([$catalog1, $catalog2]);

        $modelMock->shouldReceive('all')
            ->once()
            ->andReturn($collection);

        $results = $this->repository->findAll();

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(Catalog::class, $results);
        
        $this->assertEquals('test-id-1', $results[0]->getId());
        $this->assertEquals('Test Catalog 1', $results[0]->getNombre());
        $this->assertEquals(ServiceStatus::ACTIVO, $results[0]->getEstado());
        
        $this->assertEquals('test-id-2', $results[1]->getId());
        $this->assertEquals('Test Catalog 2', $results[1]->getNombre());
        $this->assertEquals(ServiceStatus::INACTIVO, $results[1]->getEstado());
    }

    public function testDeleteRemovesCatalog(): void
    {
        $id = 'test-id';
        $modelMock = Mockery::mock('alias:' . CatalogModel::class);
        
        $modelMock->shouldReceive('destroy')
            ->once()
            ->with($id);

        $this->repository->delete($id);
    }
} 