<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListContracts;

use Commercial\Application\Queries\ListContracts\ListContractsQuery;
use Commercial\Application\Queries\ListContracts\ListContractsHandler;
use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\ValueObjects\ContractDate;
use Commercial\Application\DTOs\ContractDTO;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ListContractsHandlerTest extends MockeryTestCase
{
	private ListContractsHandler $handler;
	private ContractRepository $repository;
	private DateTimeImmutable $fechaInicio;
	private DateTimeImmutable $fechaFin;

	protected function setUp(): void
	{
		$this->repository = Mockery::mock(ContractRepository::class);
		$this->handler = new ListContractsHandler($this->repository);

		$this->fechaInicio = new DateTimeImmutable('2024-01-01');
		$this->fechaFin = new DateTimeImmutable('2024-12-31');
	}

	public function testInvokeReturnsAllContractsWhenNoFilters(): void
	{
		$contract1 = $this->createMockContract('contract-1', 'paciente-1', 'servicio-1');
		$contract2 = $this->createMockContract('contract-2', 'paciente-2', 'servicio-2');

		$this->repository
			->shouldReceive('findAll')
			->once()
			->andReturn([$contract1, $contract2]);

		$query = new ListContractsQuery();
		$result = $this->handler->__invoke($query);

		$this->assertCount(2, $result);
		$this->assertContainsOnlyInstancesOf(ContractDTO::class, $result);
		$this->assertEquals('contract-1', $result[0]->id);
		$this->assertEquals('contract-2', $result[1]->id);
	}

	public function testInvokeFiltersByPacienteId(): void
	{
		$pacienteId = 'paciente-123';
		$contract = $this->createMockContract('contract-1', $pacienteId, 'servicio-1');

		$this->repository
			->shouldReceive('findByPacienteId')
			->once()
			->with($pacienteId)
			->andReturn([$contract]);

		$query = new ListContractsQuery($pacienteId);
		$result = $this->handler->__invoke($query);

		$this->assertCount(1, $result);
		$this->assertContainsOnlyInstancesOf(ContractDTO::class, $result);
		$this->assertEquals('contract-1', $result[0]->id);
		$this->assertEquals($pacienteId, $result[0]->pacienteId);
	}

	public function testInvokeWithPagination(): void
	{
		$contracts = [
			$this->createMockContract('contract-1', 'paciente-1', 'servicio-1'),
			$this->createMockContract('contract-2', 'paciente-2', 'servicio-2'),
			$this->createMockContract('contract-3', 'paciente-3', 'servicio-3'),
		];

		$this->repository->shouldReceive('findAll')->once()->andReturn($contracts);

		$query = new ListContractsQuery(null, 2, 1); // limit=2, offset=1
		$result = $this->handler->__invoke($query);

		$this->assertCount(2, $result);
		$this->assertEquals('contract-2', $result[0]->id);
		$this->assertEquals('contract-3', $result[1]->id);
	}

	public function testInvokeWithPacienteIdAndPagination(): void
	{
		$pacienteId = 'paciente-123';
		$contracts = [
			$this->createMockContract('contract-1', $pacienteId, 'servicio-1'),
			$this->createMockContract('contract-2', $pacienteId, 'servicio-2'),
			$this->createMockContract('contract-3', $pacienteId, 'servicio-3'),
		];

		$this->repository
			->shouldReceive('findByPacienteId')
			->once()
			->with($pacienteId)
			->andReturn($contracts);

		$query = new ListContractsQuery($pacienteId, 2, 1); // limit=2, offset=1
		$result = $this->handler->__invoke($query);

		$this->assertCount(2, $result);
		$this->assertEquals('contract-2', $result[0]->id);
		$this->assertEquals('contract-3', $result[1]->id);
	}

	public function testInvokeReturnsEmptyArrayWhenNoContracts(): void
	{
		$this->repository->shouldReceive('findAll')->once()->andReturn([]);

		$query = new ListContractsQuery();
		$result = $this->handler->__invoke($query);

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	private function createMockContract(
		string $id,
		string $pacienteId,
		string $servicioId
	): Contract {
		$contractDate = Mockery::mock(ContractDate::class);
		$contractDate->shouldReceive('getFechaInicio')->andReturn($this->fechaInicio);
		$contractDate->shouldReceive('getFechaFin')->andReturn($this->fechaFin);

		$contract = Mockery::mock(Contract::class);
		$contract->shouldReceive('getId')->andReturn($id);
		$contract->shouldReceive('getPacienteId')->andReturn($pacienteId);
		$contract->shouldReceive('getServicioId')->andReturn($servicioId);
		$contract->shouldReceive('getPlanAlimentarioId')->andReturn('plan-123');
		$contract->shouldReceive('getFechaContrato')->andReturn($contractDate);
		$contract->shouldReceive('getEstado')->andReturn('ACTIVO');

		return $contract;
	}
}
