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
use Illuminate\Support\Facades\Request;

// Fake para simular el helper request() de Laravel
if (!function_exists('Tests\\Unit\\Api\\Controllers\\request')) {
	class FakeRequest
	{
		private $params = [];
		public function setParams(array $params)
		{
			$this->params = $params;
		}
		public function get($key)
		{
			return $this->params[$key] ?? null;
		}
	}
	$GLOBALS['__fake_request'] = new FakeRequest();
	function request()
	{
		return $GLOBALS['__fake_request'];
	}
}

class ContractControllerTest extends MockeryTestCase
{
	private ContractController $controller;
	private CommandBus $commandBus;
	private QueryBus $queryBus;

	protected function setUp(): void
	{
		parent::setUp();

		// Mock de la función request() global de Laravel
		$requestMock = Mockery::mock('overload:request');
		$requestMock->shouldReceive('get')->withAnyArgs()->andReturn(null);

		$this->commandBus = Mockery::mock(CommandBus::class);
		$this->queryBus = Mockery::mock(QueryBus::class);
		$this->controller = new ContractController($this->commandBus, $this->queryBus);
	}

	public function testCreateContract(): void
	{
		$request = Mockery::mock(CreateContractRequest::class);
		$request->shouldReceive('validated')->with('paciente_id')->andReturn('paciente-123');
		$request->shouldReceive('validated')->with('servicio_id')->andReturn('servicio-456');
		$request->shouldReceive('validated')->with('fecha_inicio')->andReturn('2024-12-31');
		$request->shouldReceive('validated')->with('fecha_fin')->andReturn('2025-01-01');
		$request->shouldReceive('validated')->with('plan_alimentario_id')->andReturn(null);

		$commandResult = Mockery::mock('Commercial\Application\Commands\CommandResult');
		$commandResult->shouldReceive('isSuccess')->andReturn(true);
		$commandResult->shouldReceive('getMessage')->andReturn('Contrato creado exitosamente');
		$commandResult->shouldReceive('getId')->andReturn('contract-123');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateContractCommand::class))
			->once()
			->andReturn($commandResult);

		$contract = [
			'id' => 'contract-123',
			'pacienteId' => 'paciente-123',
			'servicioId' => 'servicio-456',
			'estado' => 'PENDIENTE',
		];
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetContractQuery::class))
			->once()
			->andReturn($contract);

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
		$request->shouldReceive('validated')->with('paciente_id')->andReturn('paciente-123');
		$request->shouldReceive('validated')->with('servicio_id')->andReturn('servicio-456');
		$request->shouldReceive('validated')->with('fecha_inicio')->andReturn('2024-12-31');
		$request->shouldReceive('validated')->with('fecha_fin')->andReturn('2025-01-01');
		$request->shouldReceive('validated')->with('plan_alimentario_id')->andReturn(null);

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
			->shouldReceive('dispatch')
			->with(Mockery::type(GetContractQuery::class))
			->once()
			->andReturn($contractData);

		$response = $this->controller->get($contractId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contractData], json_decode($response->getContent(), true));
	}

	public function testGetContractNotFound(): void
	{
		$contractId = 'contract-123';

		$this->queryBus
			->shouldReceive('dispatch')
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
			->shouldReceive('dispatch')
			->with(Mockery::type(GetContractQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Error interno');
		$this->controller->get($contractId);
	}

	public function testActivateContract(): void
	{
		$contractId = 'contract-123';

		$commandResult = Mockery::mock('Commercial\Application\Commands\CommandResult');
		$commandResult->shouldReceive('isSuccess')->andReturn(true);
		$commandResult->shouldReceive('getMessage')->andReturn('Contrato activado exitosamente');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ActivateContractCommand::class))
			->once()
			->andReturn($commandResult);

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

		$commandResult = Mockery::mock('Commercial\Application\Commands\CommandResult');
		$commandResult->shouldReceive('isSuccess')->andReturn(true);
		$commandResult->shouldReceive('getMessage')->andReturn('Contrato cancelado exitosamente');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CancelContractCommand::class))
			->once()
			->andReturn($commandResult);

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
				'id' => 'contract-1',
				'pacienteId' => $pacienteId,
				'servicioId' => 'servicio-1',
				'estado' => 'ACTIVO',
			],
			[
				'id' => 'contract-2',
				'pacienteId' => $pacienteId,
				'servicioId' => 'servicio-2',
				'estado' => 'PENDIENTE',
			],
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListContractsByPacienteQuery::class))
			->once()
			->andReturn($contracts);

		$response = $this->controller->getByPaciente($pacienteId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contracts], json_decode($response->getContent(), true));
	}

	public function testGetByPacienteHandlesException(): void
	{
		$pacienteId = 'paciente-123';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListContractsByPacienteQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Error interno');
		$this->controller->getByPaciente($pacienteId);
	}

	public function testListReturnsAllContracts(): void
	{
		// Limpiar parámetros
		request()->setParams([]);

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
			->with(
				Mockery::on(function ($query) {
					return $query instanceof
						\Commercial\Application\Queries\ListContracts\ListContractsQuery;
				})
			)
			->once()
			->andReturn($contracts);

		$response = $this->controller->list();

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contracts], json_decode($response->getContent(), true));
	}

	public function testListWithPacienteIdFilter(): void
	{
		$pacienteId = 'paciente-1';
		request()->setParams(['paciente_id' => $pacienteId]);

		$contracts = [
			[
				'id' => 'contract-1',
				'pacienteId' => $pacienteId,
				'servicioId' => 'servicio-1',
				'estado' => 'ACTIVO',
			],
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(
				Mockery::on(function ($query) {
					return $query instanceof
						\Commercial\Application\Queries\ListContracts\ListContractsQuery;
				})
			)
			->once()
			->andReturn($contracts);

		$response = $this->controller->list();

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contracts], json_decode($response->getContent(), true));
	}

	public function testListWithPagination(): void
	{
		request()->setParams(['limit' => 10, 'offset' => 0]);

		$contracts = [
			[
				'id' => 'contract-1',
				'pacienteId' => 'paciente-1',
				'servicioId' => 'servicio-1',
				'estado' => 'ACTIVO',
			],
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(
				Mockery::on(function ($query) {
					return $query instanceof
						\Commercial\Application\Queries\ListContracts\ListContractsQuery;
				})
			)
			->once()
			->andReturn($contracts);

		$response = $this->controller->list();

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(['data' => $contracts], json_decode($response->getContent(), true));
	}

	public function testListHandlesException(): void
	{
		request()->setParams([]);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(
				Mockery::on(function ($query) {
					return $query instanceof
						\Commercial\Application\Queries\ListContracts\ListContractsQuery;
				})
			)
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
