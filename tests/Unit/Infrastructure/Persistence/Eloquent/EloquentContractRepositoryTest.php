<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Infrastructure\Persistence\Eloquent\EloquentContractRepository;
use Commercial\Infrastructure\Persistence\Eloquent\ContractModel;
use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\ValueObjects\ContractDate;
use Commercial\Domain\Repositories\ServiceRepository;
use Tests\Unit\TestHelpers\DateTimeHelper;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

class EloquentContractRepositoryTest extends TestCase
{
	private EloquentContractRepository $repository;
	private ContractModel $model;
	private ServiceRepository $serviceRepository;
	private \DateTimeImmutable $futureDate;
	private \DateTimeImmutable $furtherFutureDate;
	private \DateTime $futureDateMutable;
	private \DateTime $furtherFutureDateMutable;

	protected function setUp(): void
	{
		parent::setUp();
		$this->model = Mockery::mock(ContractModel::class);
		$this->serviceRepository = Mockery::mock(ServiceRepository::class);
		$this->repository = new EloquentContractRepository($this->model, $this->serviceRepository);

		// Usar fechas que sean realmente futuras respecto a la fecha actual
		$now = new \DateTimeImmutable();
		$this->futureDate = $now->modify('+1 day');
		$this->furtherFutureDate = $now->modify('+1 year');
		$this->futureDateMutable = \DateTime::createFromImmutable($this->futureDate);
		$this->furtherFutureDateMutable = \DateTime::createFromImmutable($this->furtherFutureDate);

		// Mockear la fecha actual para que sea anterior a las fechas de prueba
		DateTimeHelper::mockNow(new \DateTimeImmutable('2024-01-01'));

		$this->model->shouldReceive('setAttribute')->andReturnSelf();
		$this->model->shouldReceive('__set')->andReturnNull();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		DateTimeHelper::reset();
	}

	public function testSaveCreatesContract(): void
	{
		$contract = Contract::create(
			'test-id',
			'paciente-123',
			'servicio-456',
			new ContractDate($this->futureDate, $this->furtherFutureDate),
			$this->serviceRepository
		);

		$mockResult = new class ($contract) extends ContractModel {
			public $id;
			private $data;
			public function __construct($contract)
			{
				$this->id = $contract->getId();
				$this->data = [
					'id' => $contract->getId(),
					'paciente_id' => $contract->getPacienteId(),
					'servicio_id' => $contract->getServicioId(),
					'plan_alimentario_id' => $contract->getPlanAlimentarioId(),
					'estado' => $contract->getEstado(),
					'fecha_inicio' => $contract->getFechaContrato()->getFechaInicio(),
					'fecha_fin' => $contract->getFechaContrato()->getFechaFin(),
				];
			}
			public function toArray()
			{
				return $this->data;
			}
		};

		$this->model
			->shouldReceive('updateOrCreate')
			->once()
			->with(
				['id' => $contract->getId()],
				[
					'paciente_id' => $contract->getPacienteId(),
					'servicio_id' => $contract->getServicioId(),
					'plan_alimentario_id' => $contract->getPlanAlimentarioId(),
					'estado' => $contract->getEstado(),
					'fecha_inicio' => $contract->getFechaContrato()->getFechaInicio(),
					'fecha_fin' => $contract->getFechaContrato()->getFechaFin(),
				]
			)
			->andReturn($mockResult);

		$this->repository->save($contract);
	}

	public function testFindByIdReturnsContractWhenExists(): void
	{
		$id = 'test-id';
		$modelMock = new class ($id, $this->futureDateMutable, $this->furtherFutureDateMutable)
			extends ContractModel
		{
			public $id;
			public $paciente_id;
			public $servicio_id;
			public $plan_alimentario_id;
			public $fecha_inicio;
			public $fecha_fin;
			private $data;

			public function __construct($id, $fechaInicio, $fechaFin)
			{
				$this->id = $id;
				$this->paciente_id = 'paciente-123';
				$this->servicio_id = 'servicio-456';
				$this->plan_alimentario_id = null;
				$this->fecha_inicio = $fechaInicio;
				$this->fecha_fin = $fechaFin;
				$this->data = [
					'id' => $id,
					'paciente_id' => 'paciente-123',
					'servicio_id' => 'servicio-456',
					'plan_alimentario_id' => null,
					'fecha_inicio' => $fechaInicio,
					'fecha_fin' => $fechaFin,
				];
			}
			public function toArray()
			{
				return $this->data;
			}
		};

		$this->model->shouldReceive('find')->once()->with($id)->andReturn($modelMock);

		$result = $this->repository->findById($id);

		$this->assertInstanceOf(Contract::class, $result);
		$this->assertEquals($id, $result->getId());
		$this->assertEquals('paciente-123', $result->getPacienteId());
		$this->assertEquals('servicio-456', $result->getServicioId());
	}

	public function testFindByIdReturnsNullWhenNotExists(): void
	{
		$id = 'non-existent-id';

		$this->model->shouldReceive('find')->once()->with($id)->andReturn(null);

		$result = $this->repository->findById($id);

		$this->assertNull($result);
	}

	public function testFindByPacienteIdReturnsContracts(): void
	{
		$pacienteId = 'paciente-123';

		$contract1 = new class (
			'test-id-1',
			$pacienteId,
			'servicio-456',
			$this->futureDateMutable,
			$this->furtherFutureDateMutable
		) extends ContractModel {
			public $id;
			public $paciente_id;
			public $servicio_id;
			public $plan_alimentario_id;
			public $fecha_inicio;
			public $fecha_fin;
			private $data;

			public function __construct($id, $pacienteId, $servicioId, $fechaInicio, $fechaFin)
			{
				$this->id = $id;
				$this->paciente_id = $pacienteId;
				$this->servicio_id = $servicioId;
				$this->plan_alimentario_id = null;
				$this->fecha_inicio = $fechaInicio;
				$this->fecha_fin = $fechaFin;
				$this->data = [
					'id' => $id,
					'paciente_id' => $pacienteId,
					'servicio_id' => $servicioId,
					'plan_alimentario_id' => null,
					'fecha_inicio' => $fechaInicio,
					'fecha_fin' => $fechaFin,
				];
			}
			public function toArray()
			{
				return $this->data;
			}
		};

		$contract2 = new class (
			'test-id-2',
			$pacienteId,
			'servicio-789',
			$this->futureDateMutable,
			$this->furtherFutureDateMutable
		) extends ContractModel {
			public $id;
			public $paciente_id;
			public $servicio_id;
			public $plan_alimentario_id;
			public $fecha_inicio;
			public $fecha_fin;
			private $data;

			public function __construct($id, $pacienteId, $servicioId, $fechaInicio, $fechaFin)
			{
				$this->id = $id;
				$this->paciente_id = $pacienteId;
				$this->servicio_id = $servicioId;
				$this->plan_alimentario_id = null;
				$this->fecha_inicio = $fechaInicio;
				$this->fecha_fin = $fechaFin;
				$this->data = [
					'id' => $id,
					'paciente_id' => $pacienteId,
					'servicio_id' => $servicioId,
					'plan_alimentario_id' => null,
					'fecha_inicio' => $fechaInicio,
					'fecha_fin' => $fechaFin,
				];
			}
			public function toArray()
			{
				return $this->data;
			}
		};

		$collection = new Collection([$contract1, $contract2]);

		$this->model->shouldReceive('where->get')->once()->andReturn($collection);

		$results = $this->repository->findByPacienteId($pacienteId);

		$this->assertCount(2, $results);
		$this->assertContainsOnlyInstancesOf(Contract::class, $results);

		$this->assertEquals('test-id-1', $results[0]->getId());
		$this->assertEquals($pacienteId, $results[0]->getPacienteId());
		$this->assertEquals('servicio-456', $results[0]->getServicioId());

		$this->assertEquals('test-id-2', $results[1]->getId());
		$this->assertEquals($pacienteId, $results[1]->getPacienteId());
		$this->assertEquals('servicio-789', $results[1]->getServicioId());
	}

	public function testFindAllReturnsAllContracts(): void
	{
		$contract1 = new class (
			'test-id-1',
			'paciente-123',
			'servicio-456',
			$this->futureDateMutable,
			$this->furtherFutureDateMutable
		) extends ContractModel {
			public $id;
			public $paciente_id;
			public $servicio_id;
			public $plan_alimentario_id;
			public $fecha_inicio;
			public $fecha_fin;
			private $data;

			public function __construct($id, $pacienteId, $servicioId, $fechaInicio, $fechaFin)
			{
				$this->id = $id;
				$this->paciente_id = $pacienteId;
				$this->servicio_id = $servicioId;
				$this->plan_alimentario_id = null;
				$this->fecha_inicio = $fechaInicio;
				$this->fecha_fin = $fechaFin;
				$this->data = [
					'id' => $id,
					'paciente_id' => $pacienteId,
					'servicio_id' => $servicioId,
					'plan_alimentario_id' => null,
					'fecha_inicio' => $fechaInicio,
					'fecha_fin' => $fechaFin,
				];
			}
			public function toArray()
			{
				return $this->data;
			}
		};

		$contract2 = new class (
			'test-id-2',
			'paciente-456',
			'servicio-789',
			$this->futureDateMutable,
			$this->furtherFutureDateMutable
		) extends ContractModel {
			public $id;
			public $paciente_id;
			public $servicio_id;
			public $plan_alimentario_id;
			public $fecha_inicio;
			public $fecha_fin;
			private $data;

			public function __construct($id, $pacienteId, $servicioId, $fechaInicio, $fechaFin)
			{
				$this->id = $id;
				$this->paciente_id = $pacienteId;
				$this->servicio_id = $servicioId;
				$this->plan_alimentario_id = null;
				$this->fecha_inicio = $fechaInicio;
				$this->fecha_fin = $fechaFin;
				$this->data = [
					'id' => $id,
					'paciente_id' => $pacienteId,
					'servicio_id' => $servicioId,
					'plan_alimentario_id' => null,
					'fecha_inicio' => $fechaInicio,
					'fecha_fin' => $fechaFin,
				];
			}
			public function toArray()
			{
				return $this->data;
			}
		};

		$collection = new Collection([$contract1, $contract2]);

		$this->model->shouldReceive('all')->once()->andReturn($collection);

		$results = $this->repository->findAll();

		$this->assertCount(2, $results);
		$this->assertContainsOnlyInstancesOf(Contract::class, $results);

		$this->assertEquals('test-id-1', $results[0]->getId());
		$this->assertEquals('paciente-123', $results[0]->getPacienteId());
		$this->assertEquals('servicio-456', $results[0]->getServicioId());

		$this->assertEquals('test-id-2', $results[1]->getId());
		$this->assertEquals('paciente-456', $results[1]->getPacienteId());
		$this->assertEquals('servicio-789', $results[1]->getServicioId());
	}

	public function testDeleteRemovesContract(): void
	{
		$id = 'test-id';

		$this->model->shouldReceive('destroy')->once()->with($id);

		$this->repository->delete($id);
	}
}
