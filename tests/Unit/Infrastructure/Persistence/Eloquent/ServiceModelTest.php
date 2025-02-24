<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Infrastructure\Persistence\Eloquent\ServiceModel;
use Commercial\Infrastructure\Persistence\Eloquent\CatalogModel;
use Commercial\Infrastructure\Persistence\Eloquent\ServiceCostModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;

class ServiceModelTest extends BaseModelTest
{
    private ServiceModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ServiceModel();
        // Establecer una fecha fija para las pruebas
        Carbon::setTestNow(Carbon::create(2024, 1, 1));
        ServiceCostModel::disableDateValidation();
    }

    protected function tearDown(): void
    {
        // Restaurar la fecha actual
        Carbon::setTestNow();
        ServiceCostModel::enableDateValidation();
        parent::tearDown();
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

        // Crear tabla de servicios
        $this->schema->create('servicios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->text('descripcion');
            $table->foreignUuid('catalogo_id')->constrained('catalogos');
            $table->string('estado');
            $table->timestamps();
            $table->softDeletes();
        });

        // Crear tabla de costos de servicios
        $this->schema->create('costo_servicios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('servicio_id')->constrained('servicios');
            $table->decimal('monto', 10, 2);
            $table->string('moneda');
            $table->date('vigencia');
            $table->timestamps();
        });
    }

    public function test_extends_eloquent_model(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function test_uses_correct_table(): void
    {
        $this->assertEquals('servicios', $this->model->getTable());
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
            'descripcion',
            'tipo_servicio_id',
            'estado',
            'catalogo_id'
        ];

        $this->assertEquals($expectedFillable, $this->model->getFillable());
    }

    public function test_dates_attributes_are_correct(): void
    {
        $expectedDates = [
            'created_at',
            'updated_at'
        ];

        $this->assertEquals($expectedDates, $this->model->getDates());
    }

    public function test_casts_are_correct(): void
    {
        $expectedCasts = [
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'costo' => 'decimal:2',
            'deleted_at' => 'datetime:Y-m-d H:i:s'
        ];

        $this->assertEquals($expectedCasts, $this->model->getCasts());
    }

    public function test_can_create_and_retrieve_service(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $serviceId = Uuid::uuid4()->toString();
        $serviceData = [
            'id' => $serviceId,
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción del servicio',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ];

        // Act
        $service = new ServiceModel($serviceData);
        $service->save();
        $retrievedService = ServiceModel::find($serviceId);

        // Assert
        $this->assertNotNull($retrievedService);
        $this->assertEquals($serviceData['nombre'], $retrievedService->nombre);
        $this->assertEquals($serviceData['descripcion'], $retrievedService->descripcion);
        $this->assertEquals($serviceData['catalogo_id'], $retrievedService->catalogo_id);
        $this->assertEquals($serviceData['estado'], $retrievedService->estado);
    }

    public function test_catalogo_relationship(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $serviceId = Uuid::uuid4()->toString();
        $service = new ServiceModel([
            'id' => $serviceId,
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción del servicio',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service->save();

        // Act
        $retrievedCatalogo = $service->catalogo;

        // Assert
        $this->assertInstanceOf(CatalogModel::class, $retrievedCatalogo);
        $this->assertEquals($catalogId, $retrievedCatalogo->id);
    }

    public function test_costos_relationship(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $serviceId = Uuid::uuid4()->toString();
        $service = new ServiceModel([
            'id' => $serviceId,
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción del servicio',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service->save();

        $costId = Uuid::uuid4()->toString();
        $costo = new ServiceCostModel([
            'id' => $costId,
            'servicio_id' => $serviceId,
            'monto' => 100.00,
            'moneda' => 'USD',
            'vigencia' => Carbon::now()
        ]);
        $costo->save();

        // Act
        $costos = $service->costos;

        // Assert
        $this->assertCount(1, $costos);
        $this->assertInstanceOf(ServiceCostModel::class, $costos->first());
        $this->assertEquals($costId, $costos->first()->id);
    }

    public function test_current_cost_returns_most_recent_valid_cost(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $serviceId = Uuid::uuid4()->toString();
        $service = new ServiceModel([
            'id' => $serviceId,
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción del servicio',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service->save();

        $costo1 = new ServiceCostModel([
            'id' => Uuid::uuid4()->toString(),
            'servicio_id' => $serviceId,
            'monto' => 100.00,
            'moneda' => 'USD',
            'vigencia' => Carbon::now()->subDay()
        ]);
        $costo1->save();

        $costo2 = new ServiceCostModel([
            'id' => Uuid::uuid4()->toString(),
            'servicio_id' => $serviceId,
            'monto' => 150.00,
            'moneda' => 'USD',
            'vigencia' => Carbon::now()
        ]);
        $costo2->save();

        // Act
        $cost = $service->currentCost();

        // Assert
        $this->assertNotNull($cost);
        $this->assertEquals(150.00, $cost->getMonto());
        $this->assertEquals('USD', $cost->getMoneda());
    }

    public function test_scope_active_filters_correctly(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $service1 = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Activo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service1->save();

        $service2 = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Inactivo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $service2->save();

        // Act
        $activeServices = ServiceModel::active()->get();

        // Assert
        $this->assertCount(1, $activeServices);
        $this->assertEquals('Servicio Activo', $activeServices->first()->nombre);
    }

    public function test_scope_by_catalog_filters_correctly(): void
    {
        // Arrange
        $catalogo1Id = Uuid::uuid4()->toString();
        $catalogo1 = new CatalogModel([
            'id' => $catalogo1Id,
            'nombre' => 'Catálogo 1',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo1->save();

        $catalogo2Id = Uuid::uuid4()->toString();
        $catalogo2 = new CatalogModel([
            'id' => $catalogo2Id,
            'nombre' => 'Catálogo 2',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo2->save();

        $service1 = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio 1',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogo1Id,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service1->save();

        $service2 = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio 2',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogo2Id,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service2->save();

        // Act
        $services = ServiceModel::byCatalog($catalogo1Id)->get();

        // Assert
        $this->assertCount(1, $services);
        $this->assertEquals('Servicio 1', $services->first()->nombre);
    }

    public function test_business_rules_methods(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $activeService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Activo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $activeService->save();

        $suspendedService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Suspendido',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::SUSPENDIDO->toString()
        ]);
        $suspendedService->save();

        // Assert
        $this->assertTrue($activeService->isActive());
        $this->assertTrue($activeService->canBeModified());
        $this->assertTrue($activeService->canUpdateCost());

        $this->assertFalse($suspendedService->isActive());
        $this->assertFalse($suspendedService->canBeModified());
        $this->assertFalse($suspendedService->canUpdateCost());
    }

    #[DataProvider('invalidStateProvider')]
    public function test_saving_with_invalid_state_throws_exception(mixed $invalidState, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $service = new ServiceModel();
        $service->fill([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => $invalidState
        ]);
        
        $service->save();
    }

    public static function invalidStateProvider(): array
    {
        return [
            'estado vacío' => [
                '',
                'El estado del servicio no puede estar vacío'
            ],
            'estado null' => [
                null,
                'El estado del servicio no puede estar vacío'
            ],
            'estado no válido' => [
                'estado_invalido',
                'Estado de servicio inválido'
            ],
            'estado numérico' => [
                123,
                'El estado del servicio debe ser una cadena de texto'
            ],
            'estado booleano' => [
                true,
                'El estado del servicio debe ser una cadena de texto'
            ]
        ];
    }

    public function test_scope_inactive_filters_correctly(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $activeService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Activo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $activeService->save();

        $inactiveService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Inactivo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $inactiveService->save();

        // Act
        $inactiveServices = ServiceModel::inactive()->get();

        // Assert
        $this->assertCount(1, $inactiveServices);
        $this->assertEquals('Servicio Inactivo', $inactiveServices->first()->nombre);
    }

    public function test_scope_discontinued_filters_correctly(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $activeService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Activo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $activeService->save();

        $discontinuedService = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Suspendido',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::SUSPENDIDO->toString()
        ]);
        $discontinuedService->save();

        // Act
        $discontinuedServices = ServiceModel::discontinued()->get();

        // Assert
        $this->assertCount(1, $discontinuedServices);
        $this->assertEquals('Servicio Suspendido', $discontinuedServices->first()->nombre);
    }

    public function test_current_cost_returns_null_when_no_costs(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $service = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service->save();

        // Act
        $cost = $service->currentCost();

        // Assert
        $this->assertNull($cost);
    }

    public function test_current_cost_returns_future_cost_when_no_current_cost(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $service = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $service->save();

        $futureCost = new ServiceCostModel([
            'id' => Uuid::uuid4()->toString(),
            'servicio_id' => $service->id,
            'monto' => 200.00,
            'moneda' => 'USD',
            'vigencia' => Carbon::now()->addDays(5)
        ]);
        $futureCost->save();

        // Act
        $cost = $service->currentCost();

        // Assert
        $this->assertNotNull($cost);
        $this->assertEquals(200.00, $cost->getMonto());
        $this->assertEquals('USD', $cost->getMoneda());
    }

    public function test_business_rules_for_suspended_service(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $service = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Suspendido',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::SUSPENDIDO->toString()
        ]);
        $service->save();

        // Assert
        $this->assertFalse($service->isActive());
        $this->assertFalse($service->canBeModified());
        $this->assertFalse($service->canUpdateCost());
    }

    public function test_business_rules_for_inactive_service(): void
    {
        // Arrange
        $catalogId = Uuid::uuid4()->toString();
        $catalogo = new CatalogModel([
            'id' => $catalogId,
            'nombre' => 'Catálogo Test',
            'estado' => ServiceStatus::ACTIVO->toString()
        ]);
        $catalogo->save();

        $service = new ServiceModel([
            'id' => Uuid::uuid4()->toString(),
            'nombre' => 'Servicio Inactivo',
            'descripcion' => 'Descripción',
            'catalogo_id' => $catalogId,
            'estado' => ServiceStatus::INACTIVO->toString()
        ]);
        $service->save();

        // Assert
        $this->assertFalse($service->isActive());
        $this->assertTrue($service->canBeModified());
        $this->assertFalse($service->canUpdateCost());
    }
} 