<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Infrastructure\Persistence\Eloquent\EloquentCatalogRepository;
use Commercial\Infrastructure\Persistence\Eloquent\CatalogModel;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Illuminate\Database\Schema\Blueprint;

class EloquentCatalogRepositoryTest extends BaseModelTest
{
    private EloquentCatalogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentCatalogRepository();
    }

    protected function createTables(): void
    {
        $this->schema->create('catalogos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->string('estado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function testSaveCreatesCatalogModel(): void
    {
        // Arrange
        $catalog = Catalog::create(
            'test-id',
            'Test Catalog',
            ServiceStatus::ACTIVO
        );

        // Act
        $this->repository->save($catalog);

        // Assert
        $savedModel = CatalogModel::find('test-id');
        $this->assertNotNull($savedModel);
        $this->assertEquals('Test Catalog', $savedModel->nombre);
        $this->assertEquals(ServiceStatus::ACTIVO->toString(), $savedModel->estado);
    }

    public function testFindByIdReturnsCatalogWhenExists(): void
    {
        // Arrange
        $model = new CatalogModel([
            'id' => 'test-id',
            'nombre' => 'Test Catalog',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $model->save();

        // Act
        $result = $this->repository->findById('test-id');

        // Assert
        $this->assertInstanceOf(Catalog::class, $result);
        $this->assertEquals('test-id', $result->getId());
        $this->assertEquals('Test Catalog', $result->getNombre());
        $this->assertEquals(ServiceStatus::ACTIVO, $result->getEstado());
    }

    public function testFindByIdReturnsNullWhenNotExists(): void
    {
        // Act
        $result = $this->repository->findById('non-existent-id');

        // Assert
        $this->assertNull($result);
    }

    public function testFindAllReturnsAllCatalogs(): void
    {
        // Arrange
        $catalog1 = new CatalogModel([
            'id' => 'test-id-1',
            'nombre' => 'Test Catalog 1',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalog1->save();

        $catalog2 = new CatalogModel([
            'id' => 'test-id-2',
            'nombre' => 'Test Catalog 2',
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $catalog2->save();

        // Act
        $results = $this->repository->findAll();

        // Assert
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
        // Arrange
        $model = new CatalogModel([
            'id' => 'test-id',
            'nombre' => 'Test Catalog',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $model->save();

        // Act
        $this->repository->delete('test-id');

        // Assert
        $this->assertNull(CatalogModel::find('test-id'));
    }
} 