<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Infrastructure\Persistence\Eloquent\CatalogModel;
use Commercial\Infrastructure\Persistence\Eloquent\ServiceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;

class CatalogModelTest extends BaseModelTest
{
    private CatalogModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CatalogModel();
    }

    protected function createTables(): void
    {
        // Crear tabla de catálogos
        $this->schema->create('catalogos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->string('estado');
            $table->timestamps();
            $table->softDeletes();
        });

        // Crear tabla de servicios (necesaria para las relaciones)
        $this->schema->create('servicios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->text('descripcion');
            $table->foreignUuid('catalogo_id')->constrained('catalogos');
            $table->string('estado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_extends_eloquent_model(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('catalogos', $this->model->getTable());
    }

    public function test_uses_uuid_configuration(): void
    {
        $this->assertEquals('string', $this->model->getKeyType());
        $this->assertFalse($this->model->getIncrementing());
    }

    public function test_fillable_attributes_are_correct(): void
    {
        $expectedFillable = [
            'id',
            'nombre',
            'estado'
        ];

        $this->assertEquals($expectedFillable, $this->model->getFillable());
    }

    public function test_casts_are_correct(): void
    {
        $expectedCasts = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];

        $this->assertEquals($expectedCasts, $this->model->getCasts());
    }

    public function test_can_create_and_retrieve_catalog(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogData = [
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ];

        // Act
        $catalog = new CatalogModel($catalogData);
        $catalog->save();
        $retrievedCatalog = CatalogModel::find($catalogId);

        // Assert
        $this->assertNotNull($retrievedCatalog);
        $this->assertEquals($catalogData['nombre'], $retrievedCatalog->nombre);
        $this->assertEquals($catalogData['estado'], $retrievedCatalog->estado);
    }

    public function test_services_relationship(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalog = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalog->save();

        $service = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción del servicio',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service->save();

        // Act
        $services = $catalog->services;

        // Assert
        $this->assertCount(1, $services);
        $this->assertInstanceOf(ServiceModel::class, $services->first());
        $this->assertEquals('Servicio Test', $services->first()->nombre);
    }

    public function test_active_services_relationship(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalog = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalog->save();

        // Servicio activo
        $activeService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Activo',
            'descripcion' => 'Descripción del servicio activo',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $activeService->save();

        // Servicio inactivo
        $inactiveService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Inactivo',
            'descripcion' => 'Descripción del servicio inactivo',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $inactiveService->save();

        // Act
        $activeServices = $catalog->activeServices;

        // Assert
        $this->assertCount(1, $activeServices);
        $this->assertEquals('Servicio Activo', $activeServices->first()->nombre);
    }

    public function test_scope_active_filters_correctly(): void
    {
        // Arrange
        $activeCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Activo',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $activeCatalog->save();

        $inactiveCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Inactivo',
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $inactiveCatalog->save();

        // Act
        $activeCatalogs = CatalogModel::active()->get();

        // Assert
        $this->assertCount(1, $activeCatalogs);
        $this->assertEquals('Catálogo Activo', $activeCatalogs->first()->nombre);
    }

    public function test_scope_inactive_filters_correctly(): void
    {
        // Arrange
        $activeCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Activo',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $activeCatalog->save();

        $inactiveCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Inactivo',
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $inactiveCatalog->save();

        // Act
        $inactiveCatalogs = CatalogModel::inactive()->get();

        // Assert
        $this->assertCount(1, $inactiveCatalogs);
        $this->assertEquals('Catálogo Inactivo', $inactiveCatalogs->first()->nombre);
    }

    public function test_scope_with_active_services_filters_correctly(): void
    {
        // Arrange
        $catalog1Id = Uuid::uuid4()->toString();
        $catalog1 = new CatalogModel([
            'id' => $catalog1Id,
            'nombre' => 'Catálogo 1',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalog1->save();

        $catalog2Id = Uuid::uuid4()->toString();
        $catalog2 = new CatalogModel([
            'id' => $catalog2Id,
            'nombre' => 'Catálogo 2',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalog2->save();

        // Servicio activo para catálogo 1
        $activeService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Activo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalog1Id,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $activeService->save();

        // Servicio inactivo para catálogo 2
        $inactiveService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Inactivo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalog2Id,
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $inactiveService->save();

        // Act
        $catalogsWithActiveServices = CatalogModel::withActiveServices()->get();

        // Assert
        $this->assertCount(1, $catalogsWithActiveServices);
        $this->assertEquals('Catálogo 1', $catalogsWithActiveServices->first()->nombre);
    }

    public function test_is_active_returns_correct_value(): void
    {
        // Arrange
        $activeCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Activo',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);

        $inactiveCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Inactivo',
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);

        // Assert
        $this->assertTrue($activeCatalog->isActive());
        $this->assertFalse($inactiveCatalog->isActive());
    }

    public function test_can_add_services_returns_correct_value(): void
    {
        // Arrange
        $activeCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Activo',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);

        $inactiveCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Inactivo',
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);

        // Assert
        $this->assertTrue($activeCatalog->canAddServices());
        $this->assertFalse($inactiveCatalog->canAddServices());
    }

    public function test_can_update_services_returns_correct_value(): void
    {
        // Arrange
        $activeCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Activo',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);

        $inactiveCatalog = new CatalogModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Inactivo',
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);

        // Assert
        $this->assertTrue($activeCatalog->canUpdateServices());
        $this->assertFalse($inactiveCatalog->canUpdateServices());
    }

    #[DataProvider('invalidStateProvider')]
    public function test_saving_with_invalid_state_throws_exception(mixed $invalidState, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $catalog = new CatalogModel();
        $catalog->fill([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Catálogo Test',
            'estado' => $invalidState
        ]);
        
        $catalog->save();
    }

    public static function invalidStateProvider(): array
    {
        return [
            'estado vacío' => [
                '',
                'El estado del catálogo no puede estar vacío'
            ],
            'estado null' => [
                null,
                'El estado del catálogo no puede estar vacío'
            ],
            'estado no válido' => [
                'estado_invalido',
                'Estado de catálogo inválido'
            ],
            'estado numérico' => [
                123,
                'El estado del catálogo debe ser una cadena de texto'
            ],
            'estado booleano' => [
                true,
                'El estado del catálogo debe ser una cadena de texto'
            ]
        ];
    }
} 