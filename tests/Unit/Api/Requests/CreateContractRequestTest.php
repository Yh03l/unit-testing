<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\CreateContractRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class CreateContractRequestTest extends TestCase
{
	use WithFaker;

	private CreateContractRequest $request;

	protected function setUp(): void
	{
		parent::setUp();
		$this->request = new CreateContractRequest();
	}

	public function testRules(): void
	{
		$rules = $this->request->rules();

		$this->assertArrayHasKey('paciente_id', $rules);
		$this->assertArrayHasKey('servicio_id', $rules);
		$this->assertArrayHasKey('fecha_inicio', $rules);
		$this->assertArrayHasKey('fecha_fin', $rules);

		$this->assertEquals('required|uuid', $rules['paciente_id']);
		$this->assertEquals('required|uuid', $rules['servicio_id']);
		$this->assertEquals('required|date', $rules['fecha_inicio']);
		$this->assertEquals('nullable|date|after:fecha_inicio', $rules['fecha_fin']);
	}

	public function testGetters(): void
	{
		$fechaInicio = new \DateTimeImmutable('2025-01-01');
		$fechaFin = new \DateTimeImmutable('2025-12-31');
		$pacienteId = $this->faker->uuid();
		$servicioId = $this->faker->uuid();

		$request = new CreateContractRequest();
		$reflection = new \ReflectionClass($request);

		$pacienteIdProperty = $reflection->getProperty('pacienteId');
		$pacienteIdProperty->setAccessible(true);
		$pacienteIdProperty->setValue($request, $pacienteId);

		$servicioIdProperty = $reflection->getProperty('servicioId');
		$servicioIdProperty->setAccessible(true);
		$servicioIdProperty->setValue($request, $servicioId);

		$fechaInicioProperty = $reflection->getProperty('fechaInicio');
		$fechaInicioProperty->setAccessible(true);
		$fechaInicioProperty->setValue($request, $fechaInicio);

		$fechaFinProperty = $reflection->getProperty('fechaFin');
		$fechaFinProperty->setAccessible(true);
		$fechaFinProperty->setValue($request, $fechaFin);

		$this->assertEquals($pacienteId, $request->validated('paciente_id'));
		$this->assertEquals($servicioId, $request->validated('servicio_id'));
		$this->assertEquals($fechaInicio, $request->validated('fecha_inicio'));
		$this->assertEquals($fechaFin, $request->validated('fecha_fin'));
	}

	public function testPrepareForValidation(): void
	{
		$pacienteId = $this->faker->uuid();
		$servicioId = $this->faker->uuid();
		$fechaInicio = '2025-01-01';
		$fechaFin = '2025-12-31';

		$request = new TestableCreateContractRequest();
		$request->setInputData([
			'paciente_id' => $pacienteId,
			'servicio_id' => $servicioId,
			'fecha_inicio' => $fechaInicio,
			'fecha_fin' => $fechaFin,
		]);

		$reflection = new \ReflectionClass($request);
		$prepareForValidation = $reflection->getMethod('prepareForValidation');
		$prepareForValidation->setAccessible(true);
		$prepareForValidation->invoke($request);

		$this->assertEquals($pacienteId, $request->validated('paciente_id'));
		$this->assertEquals($servicioId, $request->validated('servicio_id'));
		$this->assertEquals(
			new \DateTimeImmutable($fechaInicio),
			$request->validated('fecha_inicio')
		);
		$this->assertEquals(new \DateTimeImmutable($fechaFin), $request->validated('fecha_fin'));
	}

	public function testPrepareForValidationWithoutFechaFin(): void
	{
		$pacienteId = $this->faker->uuid();
		$servicioId = $this->faker->uuid();
		$fechaInicio = '2025-01-01';

		$request = new TestableCreateContractRequest();
		$request->setInputData([
			'paciente_id' => $pacienteId,
			'servicio_id' => $servicioId,
			'fecha_inicio' => $fechaInicio,
		]);

		$reflection = new \ReflectionClass($request);
		$prepareForValidation = $reflection->getMethod('prepareForValidation');
		$prepareForValidation->setAccessible(true);
		$prepareForValidation->invoke($request);

		$this->assertEquals($pacienteId, $request->validated('paciente_id'));
		$this->assertEquals($servicioId, $request->validated('servicio_id'));
		$this->assertEquals(
			new \DateTimeImmutable($fechaInicio),
			$request->validated('fecha_inicio')
		);
		$this->assertNull($request->validated('fecha_fin'));
	}
}

class TestableCreateContractRequest extends CreateContractRequest
{
	private array $inputData = [];

	public function setInputData(array $data): void
	{
		$this->inputData = $data;
	}

	public function input($key = null, $default = null)
	{
		if ($key === null) {
			return $this->inputData;
		}

		return $this->inputData[$key] ?? $default;
	}
}
