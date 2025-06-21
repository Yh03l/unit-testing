<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Controllers;

use Commercial\Api\Controllers\ContractController;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Application\Commands\CreateContract\CreateContractCommand;
use Commercial\Application\Commands\ActivateContract\ActivateContractCommand;
use Commercial\Application\Commands\CancelContract\CancelContractCommand;
use Commercial\Application\Queries\GetContract\GetContractQuery;
use Commercial\Application\Queries\ListContractsByPaciente\ListContractsByPacienteQuery;
use Commercial\Api\Requests\CreateContractRequest;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Illuminate\Http\Response;
use DateTimeImmutable;

class ContractControllerTest extends MockeryTestCase
{
	private ContractController $controller;
	private CommandBus $commandBus;
	private QueryBus $queryBus;

	protected function setUp(): void
	{
		parent::setUp();
		$this->commandBus = Mockery::mock(CommandBus::class);
		$this->queryBus = Mockery::mock(QueryBus::class);
		$this->controller = new ContractController($this->commandBus, $this->queryBus);
	}

	public function testCreateContract(): void
	{
		$request = Mockery::mock(CreateContractRequest::class);
		$request->shouldReceive('getPacienteId')->andReturn('paciente-123');
		$request->shouldReceive('getServicioId')->andReturn('servicio-456');
		$request->shouldReceive('getFechaInicio')->andReturn(new DateTimeImmutable('tomorrow'));
		$request->shouldReceive('getFechaFin')->andReturn(new DateTimeImmutable('next week'));

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateContractCommand::class))
			->once();

		$response = $this->controller->create($request);

		$this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
		$this->assertEquals(
			'Contrato creado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
	}

	public function testCreateContractHandlesDomainException(): void
	{
		$request = Mockery::mock(CreateContractRequest::class);
		$request->shouldReceive('getPacienteId')->andReturn('paciente-123');
		$request->shouldReceive('getServicioId')->andReturn('servicio-456');
		$request->shouldReceive('getFechaInicio')->andReturn(new DateTimeImmutable('tomorrow'));
		$request->shouldReceive('getFechaFin')->andReturn(new DateTimeImmutable('next week'));

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateContractCommand::class))
			->once()
			->andThrow(new \DomainException('Error en la creación del contrato'));

		$response = $this->controller->create($request);

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertEquals(
			'Error en la creación del contrato',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testGetContract(): void
	{
		$contractId = 'contract-123';
		$contractData = [
			'id' => $contractId,
			'pacienteId' => 'paciente-123',
			'servicioId' => 'servicio-456',
			'estado' => 'PENDIENTE',
		];

		$this->queryBus
			->shouldReceive('ask')
			->with(Mockery::type(GetContractQuery::class))
			->once()
			->andReturn($contractData);

		$response = $this->controller->get($contractId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals($contractData, json_decode($response->getContent(), true));
	}

	public function testGetContractNotFound(): void
	{
		$contractId = 'contract-123';

		$this->queryBus
			->shouldReceive('ask')
			->with(Mockery::type(GetContractQuery::class))
			->once()
			->andReturn(null);

		$response = $this->controller->get($contractId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Contrato no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testGetContractHandlesException(): void
	{
		$contractId = 'contract-123';

		$this->queryBus
			->shouldReceive('ask')
			->with(Mockery::type(GetContractQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->get($contractId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals('Error interno', json_decode($response->getContent(), true)['error']);
	}

	public function testActivateContract(): void
	{
		$contractId = 'contract-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ActivateContractCommand::class))
			->once();

		$response = $this->controller->activate($contractId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(
			'Contrato activado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
	}

	public function testActivateContractHandlesDomainException(): void
	{
		$contractId = 'contract-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ActivateContractCommand::class))
			->once()
			->andThrow(new \DomainException('Error al activar el contrato'));

		$response = $this->controller->activate($contractId);

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertEquals(
			'Error al activar el contrato',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testCancelContract(): void
	{
		$contractId = 'contract-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CancelContractCommand::class))
			->once();

		$response = $this->controller->cancel($contractId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(
			'Contrato cancelado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
	}

	public function testCancelContractHandlesDomainException(): void
	{
		$contractId = 'contract-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CancelContractCommand::class))
			->once()
			->andThrow(new \DomainException('Error al cancelar el contrato'));

		$response = $this->controller->cancel($contractId);

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertEquals(
			'Error al cancelar el contrato',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testGetByPaciente(): void
	{
		$pacienteId = 'paciente-123';
		$contracts = [
			[
				'id' => 'contract-123',
				'pacienteId' => $pacienteId,
				'servicioId' => 'servicio-456',
				'estado' => 'ACTIVO',
			],
			[
				'id' => 'contract-456',
				'pacienteId' => $pacienteId,
				'servicioId' => 'servicio-789',
				'estado' => 'PENDIENTE',
			],
		];

		$this->queryBus
			->shouldReceive('ask')
			->with(Mockery::type(ListContractsByPacienteQuery::class))
			->once()
			->andReturn($contracts);

		$response = $this->controller->getByPaciente($pacienteId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals($contracts, json_decode($response->getContent(), true));
	}

	public function testGetByPacienteHandlesException(): void
	{
		$pacienteId = 'paciente-123';

		$this->queryBus
			->shouldReceive('ask')
			->with(Mockery::type(ListContractsByPacienteQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->getByPaciente($pacienteId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals('Error interno', json_decode($response->getContent(), true)['error']);
	}

	public function testListReturnsAllContracts(): void
	{
		$contracts = [
			[
				'id' => 'contract-1',
				'pacienteId' => 'paciente-1',
				'servicioId' => 'servicio-1',
				'estado' => 'ACTIVO',
			],
			[
				'id' => 'contract-2',
				'pacienteId' => 'paciente-2',
				'servicioId' => 'servicio-2',
				'estado' => 'PENDIENTE',
			],
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListContractsQuery::class))
			->once()
			->andReturn($contracts);

		$response = $this->controller->list();

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contracts], json_decode($response->getContent(), true));
	}

	public function testListWithPacienteIdFilter(): void
	{
		$pacienteId = 'paciente-123';
		$contracts = [
			[
				'id' => 'contract-1',
				'pacienteId' => $pacienteId,
				'servicioId' => 'servicio-1',
				'estado' => 'ACTIVO',
			],
		];

		// Mock request parameters
		request()->merge(['paciente_id' => $pacienteId]);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListContractsQuery::class))
			->once()
			->andReturn($contracts);

		$response = $this->controller->list();

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contracts], json_decode($response->getContent(), true));
	}

	public function testListWithPagination(): void
	{
		$contracts = [
			[
				'id' => 'contract-1',
				'pacienteId' => 'paciente-1',
				'servicioId' => 'servicio-1',
				'estado' => 'ACTIVO',
			],
		];

		// Mock request parameters
		request()->merge(['limit' => 10, 'offset' => 0]);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListContractsQuery::class))
			->once()
			->andReturn($contracts);

		$response = $this->controller->list();

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contracts], json_decode($response->getContent(), true));
	}

	public function testListHandlesException(): void
	{
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListContractsQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->list();

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error interno al listar los contratos: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}
}
