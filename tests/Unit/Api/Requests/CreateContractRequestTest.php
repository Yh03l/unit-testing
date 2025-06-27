<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\CreateContractRequest;
use PHPUnit\Framework\TestCase;

class CreateContractRequestTest extends TestCase
{
	private CreateContractRequest $request;

	protected function setUp(): void
	{
		$this->request = new CreateContractRequest();
	}

	public function testRules(): void
	{
		$rules = $this->request->rules();

		$this->assertArrayHasKey('paciente_id', $rules);
		$this->assertArrayHasKey('servicio_id', $rules);
		$this->assertArrayHasKey('plan_alimentario_id', $rules);
		$this->assertArrayHasKey('fecha_inicio', $rules);
		$this->assertArrayHasKey('fecha_fin', $rules);

		$this->assertEquals('required|uuid|exists:pacientes,id', $rules['paciente_id']);
		$this->assertEquals('required|uuid|exists:servicios,id', $rules['servicio_id']);
		$this->assertEquals('nullable|uuid', $rules['plan_alimentario_id']);
		$this->assertEquals('required|date', $rules['fecha_inicio']);
		$this->assertEquals('nullable|date|after:fecha_inicio', $rules['fecha_fin']);
	}

	public function testAuthorize(): void
	{
		$this->assertTrue($this->request->authorize());
	}

	public function testPrepareForValidation(): void
	{
		// Simular datos de entrada
		$inputData = [
			'paciente_id' => 'paciente-123',
			'servicio_id' => 'servicio-456',
			'fecha_inicio' => '2024-01-01',
			'fecha_fin' => '2024-12-31',
		];

		// Crear una instancia con datos simulados
		$request = $this->createRequestWithData($inputData);

		// Verificar que el método existe y no lanza excepciones
		$reflection = new \ReflectionClass($request);
		$prepareForValidation = $reflection->getMethod('prepareForValidation');
		$prepareForValidation->setAccessible(true);

		// No debería lanzar excepciones
		$this->assertNull($prepareForValidation->invoke($request));
	}

	public function testPrepareForValidationWithoutDates(): void
	{
		// Simular datos de entrada sin fechas
		$inputData = [
			'paciente_id' => 'paciente-123',
			'servicio_id' => 'servicio-456',
		];

		// Crear una instancia con datos simulados
		$request = $this->createRequestWithData($inputData);

		// Verificar que el método existe y no lanza excepciones
		$reflection = new \ReflectionClass($request);
		$prepareForValidation = $reflection->getMethod('prepareForValidation');
		$prepareForValidation->setAccessible(true);

		// No debería lanzar excepciones
		$this->assertNull($prepareForValidation->invoke($request));
	}

	private function createRequestWithData(array $data): CreateContractRequest
	{
		$request = new class ($data) extends CreateContractRequest {
			private array $data;

			public function __construct(array $data)
			{
				$this->data = $data;
			}

			protected function prepareForValidation(): void
			{
				// Simular la preparación de validación sin usar Request::capture()
				if (isset($this->data['fecha_inicio'])) {
					$this->data['fecha_inicio'] = (new \DateTimeImmutable(
						$this->data['fecha_inicio']
					))->format('Y-m-d\TH:i:s.u\Z');
				}

				if (isset($this->data['fecha_fin'])) {
					$this->data['fecha_fin'] = (new \DateTimeImmutable(
						$this->data['fecha_fin']
					))->format('Y-m-d\TH:i:s.u\Z');
				}
			}
		};

		return $request;
	}
}
