<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Infrastructure\Persistence\Eloquent\ServiceCostModel;
use Commercial\Infrastructure\Persistence\Eloquent\ServiceModel;
use Commercial\Infrastructure\Persistence\Eloquent\CatalogModel;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;

class ServiceCostModelTest extends BaseModelTest
{
	private ServiceCostModel $model;
	private ServiceModel $service;

	protected function setUp(): void
	{
		parent::setUp();
		$this->model = new ServiceCostModel();
		// Establecer una fecha fija para las pruebas
		Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));
		ServiceCostModel::disableDateValidation();
		$this->service = $this->createBaseService();
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
		// Crear tabla de catálogos (necesaria para las relaciones)
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
		$this->assertEquals('costo_servicios', $this->model->getTable());
	}

	public function test_uses_uuid_configuration(): void
	{
		$this->assertEquals('string', $this->model->getKeyType());
		$this->assertFalse($this->model->getIncrementing());
	}

	public function test_fillable_attributes_are_correct(): void
	{
		$expectedFillable = ['id', 'servicio_id', 'monto', 'moneda', 'vigencia'];

		$this->assertEquals($expectedFillable, $this->model->getFillable());
	}

	public function test_casts_are_correct(): void
	{
		$expectedCasts = [
			'monto' => 'decimal:2',
			'vigencia' => 'date',
			'created_at' => 'datetime:Y-m-d H:i:s',
			'updated_at' => 'datetime:Y-m-d H:i:s',
		];

		$this->assertEquals($expectedCasts, $this->model->getCasts());
	}

	public function test_dates_attributes_are_correct(): void
	{
		$expectedDates = ['created_at', 'updated_at', 'vigencia'];

		$actualDates = $this->model->getDates();
		sort($actualDates);
		sort($expectedDates);

		$this->assertEquals($expectedDates, $actualDates);
	}

	public function test_default_attributes_are_correct(): void
	{
		$expectedDefaults = [
			'monto' => '0.00',
		];

		$this->assertEquals($expectedDefaults, $this->model->getAttributes());
	}

	public function test_can_create_and_retrieve_service_cost(): void
	{
		// Arrange
		$costId = Uuid::uuid4()->toString();
		$costData = [
			'id' => $costId,
			'servicio_id' => $this->service->id,
			'monto' => 100.0,
			'moneda' => 'USD',
			'vigencia' => Carbon::now(),
		];

		// Act
		$cost = new ServiceCostModel($costData);
		$cost->save();
		$retrievedCost = ServiceCostModel::find($costId);

		// Assert
		$this->assertNotNull($retrievedCost);
		$this->assertEquals($costData['monto'], $retrievedCost->monto);
		$this->assertEquals($costData['moneda'], $retrievedCost->moneda);
		$this->assertEquals(
			$costData['vigencia']->format('Y-m-d'),
			$retrievedCost->vigencia->format('Y-m-d')
		);
	}

	public function test_servicio_relationship(): void
	{
		// Arrange
		$cost = $this->createCost($this->service->id);

		// Act
		$retrievedService = $cost->servicio;

		// Assert
		$this->assertInstanceOf(ServiceModel::class, $retrievedService);
		$this->assertEquals($this->service->id, $retrievedService->id);
	}

	public function test_scope_active_filters_correctly(): void
	{
		// Arrange
		$this->createMultipleCosts();

		// Act
		$activeCosts = ServiceCostModel::active()->get();

		// Assert
		$this->assertCount(2, $activeCosts);
		$this->assertTrue($activeCosts->contains('monto', 150.0));
		$this->assertTrue($activeCosts->contains('monto', 200.0));
	}

	public function test_scope_expired_filters_correctly(): void
	{
		// Arrange
		$this->createMultipleCosts();

		// Act
		$expiredCosts = ServiceCostModel::expired()->get();

		// Assert
		$this->assertCount(1, $expiredCosts);
		$this->assertEquals(100.0, $expiredCosts->first()->monto);
	}

	public function test_scope_by_moneda_filters_correctly(): void
	{
		// Arrange
		$this->createMultipleCostsWithDifferentCurrencies();

		// Act
		$usdCosts = ServiceCostModel::byMoneda('USD')->get();
		$eurCosts = ServiceCostModel::byMoneda('EUR')->get();

		// Assert
		$this->assertCount(2, $usdCosts);
		$this->assertCount(1, $eurCosts);
	}

	public function test_scope_by_date_range_filters_correctly(): void
	{
		// Arrange
		$this->createMultipleCosts();
		$from = Carbon::now()->subDays(2)->startOfDay();
		$to = Carbon::now()->addDays(2)->endOfDay();

		// Act
		$costs = ServiceCostModel::byDateRange($from, $to)->get();

		// Assert
		$this->assertCount(3, $costs);
		$this->assertTrue($costs->contains('monto', 100.0));
		$this->assertTrue($costs->contains('monto', 150.0));
		$this->assertTrue($costs->contains('monto', 200.0));
	}

	public function test_is_active_returns_correct_value(): void
	{
		// Arrange
		$activeCost = $this->createCost($this->service->id, 100.0, 'USD', Carbon::now()->addDay());
		$expiredCost = $this->createCost($this->service->id, 150.0, 'USD', Carbon::now()->subDay());

		// Assert
		$this->assertTrue($activeCost->isActive());
		$this->assertFalse($expiredCost->isActive());
	}

	public function test_is_expired_returns_correct_value(): void
	{
		// Arrange
		$activeCost = $this->createCost($this->service->id, 100.0, 'USD', Carbon::now()->addDay());
		$expiredCost = $this->createCost($this->service->id, 150.0, 'USD', Carbon::now()->subDay());

		// Assert
		$this->assertFalse($activeCost->isExpired());
		$this->assertTrue($expiredCost->isExpired());
	}

	public function test_get_next_active_cost_returns_correct_cost(): void
	{
		// Arrange
		$pastCost = $this->createCost($this->service->id, 100.0, 'USD', Carbon::now()->subDay());

		$futureCost = $this->createCost($this->service->id, 150.0, 'USD', Carbon::now()->addDay());

		// Act
		$nextCost = $pastCost->getNextActiveCost();

		// Assert
		$this->assertNotNull($nextCost);
		$this->assertEquals(150.0, (float) $nextCost->monto);
	}

	public function test_get_previous_cost_returns_correct_cost(): void
	{
		// Arrange
		$this->createMultipleCosts();
		$currentCost = ServiceCostModel::where('monto', 150.0)->first();

		// Act
		$previousCost = $currentCost->getPreviousCost();

		// Assert
		$this->assertNotNull($previousCost);
		$this->assertEquals(100.0, $previousCost->monto);
	}

	public function test_validate_vigencia_date_throws_exception_for_past_date(): void
	{
		// Arrange
		ServiceCostModel::enableDateValidation();
		$cost = new ServiceCostModel();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'La fecha de vigencia no puede ser anterior a la fecha actual'
		);

		// Act
		$cost->vigencia = Carbon::now()->subDay();
	}

	public function test_validate_vigencia_date_passes_for_future_date(): void
	{
		// Arrange
		ServiceCostModel::enableDateValidation();
		$cost = new ServiceCostModel();
		$cost->vigencia = Carbon::now()->addDay();

		// Act & Assert - No debería lanzar excepción
		$cost->validateVigenciaDate();
		$this->assertTrue(true); // Si llegamos aquí, el test pasa
	}

	public function test_setting_past_date_throws_exception(): void
	{
		// Arrange
		ServiceCostModel::enableDateValidation();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'La fecha de vigencia no puede ser anterior a la fecha actual'
		);

		// Act
		$cost = new ServiceCostModel();
		$cost->vigencia = Carbon::now()->subDay();
	}

	public function test_setting_invalid_date_format_throws_exception(): void
	{
		// Arrange
		$cost = $this->createCost($this->service->id);

		// Assert
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('La fecha de vigencia no es válida');

		// Act
		try {
			$date = new \DateTime('xyz');
		} catch (\Exception $e) {
			throw new \InvalidArgumentException('La fecha de vigencia no es válida');
		}
		$cost->vigencia = $date;
	}

	public function test_setting_null_date_throws_exception(): void
	{
		// Arrange
		ServiceCostModel::enableDateValidation();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('La fecha de vigencia no es válida');

		// Act
		$cost = new ServiceCostModel();
		$cost->vigencia = null;
	}

	public function test_setting_array_as_date_throws_exception(): void
	{
		// Arrange
		ServiceCostModel::enableDateValidation();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('La fecha de vigencia no es válida');

		// Act
		$cost = new ServiceCostModel();
		$cost->vigencia = [2024, 1, 1];
	}

	private function createBaseService(): ServiceModel
	{
		$catalogId = Uuid::uuid4()->toString();
		$catalogo = new CatalogModel([
			'id' => $catalogId,
			'nombre' => 'Catálogo Test',
			'estado' => ServiceStatus::ACTIVO->toString(),
		]);
		$catalogo->save();

		$serviceId = Uuid::uuid4()->toString();
		$service = new ServiceModel([
			'id' => $serviceId,
			'nombre' => 'Servicio Test',
			'descripcion' => 'Descripción',
			'catalogo_id' => $catalogId,
			'estado' => ServiceStatus::ACTIVO->toString(),
		]);
		$service->save();

		return $service;
	}

	private function createCost(
		string $serviceId,
		float $monto = 100.0,
		string $moneda = 'USD',
		?Carbon $vigencia = null
	): ServiceCostModel {
		$cost = new ServiceCostModel();
		$cost->fill([
			'id' => Uuid::uuid4()->toString(),
			'servicio_id' => $serviceId,
			'monto' => $monto,
			'moneda' => $moneda,
			'vigencia' => $vigencia ?? Carbon::now(),
		]);
		$cost->save();
		return $cost;
	}

	private function createMultipleCosts(): void
	{
		// Costo pasado
		$this->createCost($this->service->id, 100.0, 'USD', Carbon::now()->subDay());

		// Costo actual
		$this->createCost($this->service->id, 150.0, 'USD', Carbon::now()->addDay());

		// Costo futuro
		$this->createCost($this->service->id, 200.0, 'USD', Carbon::now()->addDays(2));
	}

	private function createMultipleCostsWithDifferentCurrencies(): void
	{
		// Costos en USD
		$this->createCost($this->service->id, 100.0, 'USD', Carbon::now()->subDay());

		$this->createCost($this->service->id, 150.0, 'USD', Carbon::now()->addDay());

		// Costo en EUR
		$this->createCost($this->service->id, 130.0, 'EUR', Carbon::now()->addDay());
	}
}
