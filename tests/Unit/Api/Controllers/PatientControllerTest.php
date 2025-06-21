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
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PatientControllerTest extends MockeryTestCase
{
	private PatientController $controller;
	private CommandBus $commandBus;
	private QueryBus $queryBus;

	protected function setUp(): void
	{
		$this->commandBus = Mockery::mock(CommandBus::class);
		$this->queryBus = Mockery::mock(QueryBus::class);
		$this->controller = new PatientController($this->commandBus, $this->queryBus);
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
		$this->queryBus->shouldReceive('dispatch')->once()->andReturn([]);

		$response = $this->controller->list();

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(['data' => []], $response->getData(true));
	}
}
