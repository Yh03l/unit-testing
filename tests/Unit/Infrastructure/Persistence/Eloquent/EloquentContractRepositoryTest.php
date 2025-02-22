<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Infrastructure\Persistence\Eloquent\EloquentContractRepository;
use Commercial\Infrastructure\Persistence\Eloquent\ContractModel;
use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\ValueObjects\ContractDate;
use Tests\Unit\TestHelpers\DateTimeHelper;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Illuminate\Database\Eloquent\Collection;

class EloquentContractRepositoryTest extends MockeryTestCase
{
    private EloquentContractRepository $repository;
    private ContractModel $model;
    private \DateTimeImmutable $futureDate;
    private \DateTimeImmutable $furtherFutureDate;
    private \DateTime $futureDateMutable;
    private \DateTime $furtherFutureDateMutable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Mockery::mock(ContractModel::class);
        $this->repository = new EloquentContractRepository($this->model);
        
        // Usar fechas futuras para evitar validaciones
        $this->futureDate = new \DateTimeImmutable('2025-01-01');
        $this->furtherFutureDate = new \DateTimeImmutable('2025-12-31');
        $this->futureDateMutable = \DateTime::createFromImmutable($this->futureDate);
        $this->furtherFutureDateMutable = \DateTime::createFromImmutable($this->furtherFutureDate);

        // Mockear la fecha actual para que sea anterior a las fechas de prueba
        DateTimeHelper::mockNow(new \DateTimeImmutable('2024-01-01'));
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
            new ContractDate(
                $this->futureDate,
                $this->furtherFutureDate
            )
        );

        $this->model->shouldReceive('updateOrCreate')
            ->once()
            ->with(
                ['id' => $contract->getId()],
                [
                    'paciente_id' => $contract->getPacienteId(),
                    'servicio_id' => $contract->getServicioId(),
                    'estado' => $contract->getEstado(),
                    'fecha_inicio' => $contract->getFechaContrato()->getFechaInicio(),
                    'fecha_fin' => $contract->getFechaContrato()->getFechaFin(),
                ]
            );

        $this->repository->save($contract);
    }

    public function testFindByIdReturnsContractWhenExists(): void
    {
        $id = 'test-id';
        $modelMock = Mockery::mock(ContractModel::class);
        
        $modelMock->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn($id);
        $modelMock->shouldReceive('getAttribute')
            ->with('paciente_id')
            ->andReturn('paciente-123');
        $modelMock->shouldReceive('getAttribute')
            ->with('servicio_id')
            ->andReturn('servicio-456');
        $modelMock->shouldReceive('getAttribute')
            ->with('fecha_inicio')
            ->andReturn($this->futureDateMutable);
        $modelMock->shouldReceive('getAttribute')
            ->with('fecha_fin')
            ->andReturn($this->furtherFutureDateMutable);

        $this->model->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($modelMock);

        $result = $this->repository->findById($id);

        $this->assertInstanceOf(Contract::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals('paciente-123', $result->getPacienteId());
        $this->assertEquals('servicio-456', $result->getServicioId());
    }

    public function testFindByIdReturnsNullWhenNotExists(): void
    {
        $id = 'non-existent-id';
        
        $this->model->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(null);

        $result = $this->repository->findById($id);

        $this->assertNull($result);
    }

    public function testFindByPacienteIdReturnsContracts(): void
    {
        $pacienteId = 'paciente-123';
        
        $contract1 = Mockery::mock(ContractModel::class);
        $contract1->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn('test-id-1');
        $contract1->shouldReceive('getAttribute')
            ->with('paciente_id')
            ->andReturn($pacienteId);
        $contract1->shouldReceive('getAttribute')
            ->with('servicio_id')
            ->andReturn('servicio-456');
        $contract1->shouldReceive('getAttribute')
            ->with('fecha_inicio')
            ->andReturn($this->futureDateMutable);
        $contract1->shouldReceive('getAttribute')
            ->with('fecha_fin')
            ->andReturn($this->furtherFutureDateMutable);

        $contract2 = Mockery::mock(ContractModel::class);
        $contract2->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn('test-id-2');
        $contract2->shouldReceive('getAttribute')
            ->with('paciente_id')
            ->andReturn($pacienteId);
        $contract2->shouldReceive('getAttribute')
            ->with('servicio_id')
            ->andReturn('servicio-789');
        $contract2->shouldReceive('getAttribute')
            ->with('fecha_inicio')
            ->andReturn($this->futureDateMutable);
        $contract2->shouldReceive('getAttribute')
            ->with('fecha_fin')
            ->andReturn($this->furtherFutureDateMutable);

        $collection = new Collection([$contract1, $contract2]);

        $this->model->shouldReceive('where->get')
            ->once()
            ->andReturn($collection);

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
        $contract1 = Mockery::mock(ContractModel::class);
        $contract1->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn('test-id-1');
        $contract1->shouldReceive('getAttribute')
            ->with('paciente_id')
            ->andReturn('paciente-123');
        $contract1->shouldReceive('getAttribute')
            ->with('servicio_id')
            ->andReturn('servicio-456');
        $contract1->shouldReceive('getAttribute')
            ->with('fecha_inicio')
            ->andReturn($this->futureDateMutable);
        $contract1->shouldReceive('getAttribute')
            ->with('fecha_fin')
            ->andReturn($this->furtherFutureDateMutable);

        $contract2 = Mockery::mock(ContractModel::class);
        $contract2->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn('test-id-2');
        $contract2->shouldReceive('getAttribute')
            ->with('paciente_id')
            ->andReturn('paciente-789');
        $contract2->shouldReceive('getAttribute')
            ->with('servicio_id')
            ->andReturn('servicio-012');
        $contract2->shouldReceive('getAttribute')
            ->with('fecha_inicio')
            ->andReturn($this->futureDateMutable);
        $contract2->shouldReceive('getAttribute')
            ->with('fecha_fin')
            ->andReturn($this->furtherFutureDateMutable);

        $collection = new Collection([$contract1, $contract2]);

        $this->model->shouldReceive('all')
            ->once()
            ->andReturn($collection);

        $results = $this->repository->findAll();

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(Contract::class, $results);
        
        $this->assertEquals('test-id-1', $results[0]->getId());
        $this->assertEquals('paciente-123', $results[0]->getPacienteId());
        $this->assertEquals('servicio-456', $results[0]->getServicioId());
        
        $this->assertEquals('test-id-2', $results[1]->getId());
        $this->assertEquals('paciente-789', $results[1]->getPacienteId());
        $this->assertEquals('servicio-012', $results[1]->getServicioId());
    }

    public function testDeleteRemovesContract(): void
    {
        $id = 'test-id';
        
        $this->model->shouldReceive('destroy')
            ->once()
            ->with($id);

        $this->repository->delete($id);
    }
} 