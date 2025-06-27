<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Controllers;

use Commercial\Api\Controllers\PatientController;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Application\Commands\CommandResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
	use MockeryPHPUnitIntegration;

	private PatientController $controller;
	private CommandBus $commandBus;
	private QueryBus $queryBus;

	protected function setUp(): void
	{
		parent::setUp();
		$this->commandBus = Mockery::mock(CommandBus::class);
		$this->queryBus = Mockery::mock(QueryBus::class);
		$this->controller = new PatientController($this->commandBus, $this->queryBus);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}

	// Helper para simular request() de Laravel
	private function mockRequest(array $data = []): void
	{
		// Limpiar cualquier función request previa
		if (function_exists('request')) {
			// No podemos eliminar funciones en PHP, pero podemos redefinir
		}

		// Crear una nueva función request
		eval(
			'if (!function_exists("request")) { 
			function request($key = null, $default = null) { 
				static $requestData = []; 
				if (empty($requestData)) { 
					$requestData = ' .
				var_export($data, true) .
				'; 
				} 
				if ($key === null) { 
					return (object)["get" => function($k, $d = null) use ($requestData) { 
						return $requestData[$k] ?? $d; 
					}]; 
				} 
				return $requestData[$key] ?? $default; 
			} 
		}'
		);
	}

	public function testGetReturnsPatientWhenFound(): void
	{
		$patientId = 'patient-123';
		$patientData = [
			'id' => $patientId,
			'nombre' => 'John',
			'apellido' => 'Doe',
			'email' => 'john.doe@example.com',
			'tipo' => 'PACIENTE',
			'estado' => 'activo',
		];

		$this->queryBus->shouldReceive('dispatch')->once()->andReturn($patientData);

		$response = $this->controller->get($patientId);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(['data' => $patientData], $response->getData(true));
	}

	public function testGetReturnsNotFoundWhenPatientNotExists(): void
	{
		$patientId = 'patient-123';

		$this->queryBus->shouldReceive('dispatch')->once()->andReturn(null);

		$response = $this->controller->get($patientId);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(404, $response->getStatusCode());
		$this->assertEquals(['error' => 'Paciente no encontrado'], $response->getData(true));
	}

	public function testListReturnsPatientsList(): void
	{
		$this->mockRequest();

		$patientsData = [
			[
				'id' => 'patient-1',
				'nombre' => 'John',
				'apellido' => 'Doe',
				'email' => 'john.doe@example.com',
			],
			[
				'id' => 'patient-2',
				'nombre' => 'Jane',
				'apellido' => 'Smith',
				'email' => 'jane.smith@example.com',
			],
		];

		$this->queryBus->shouldReceive('dispatch')->once()->andReturn($patientsData);

		$response = $this->controller->list();

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(['data' => $patientsData], $response->getData(true));
	}

	public function testListReturnsEmptyArrayWhenNoPatients(): void
	{
		$this->mockRequest();

		$this->queryBus->shouldReceive('dispatch')->once()->andReturn([]);

		$response = $this->controller->list();

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(['data' => []], $response->getData(true));
	}

	public function testListWithPagination(): void
	{
		$this->mockRequest(['limit' => '10', 'offset' => '5']);

		$patientsData = [
			[
				'id' => 'patient-1',
				'nombre' => 'John',
				'apellido' => 'Doe',
				'email' => 'john.doe@example.com',
			],
		];

		$this->queryBus->shouldReceive('dispatch')->once()->andReturn($patientsData);

		$response = $this->controller->list();

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(['data' => $patientsData], $response->getData(true));
	}
}
