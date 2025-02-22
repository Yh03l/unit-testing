<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListContractsByPaciente;

use Commercial\Application\Queries\ListContractsByPaciente\ListContractsByPacienteHandler;
use Commercial\Application\Queries\ListContractsByPaciente\ListContractsByPacienteQuery;
use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\ValueObjects\ContractDate;
use Commercial\Application\DTOs\ContractDTO;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class ListContractsByPacienteHandlerTest extends MockeryTestCase
{
    private ListContractsByPacienteHandler $handler;
    private ContractRepository|MockInterface $repository;
    private string $pacienteId;
    private ListContractsByPacienteQuery $query;
    private DateTimeImmutable $fechaInicio;
    private DateTimeImmutable $fechaFin;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(ContractRepository::class);
        $this->handler = new ListContractsByPacienteHandler($this->repository);
        
        $this->pacienteId = 'paciente-123';
        $this->query = new ListContractsByPacienteQuery($this->pacienteId);
        
        $this->fechaInicio = new DateTimeImmutable('2024-01-01');
        $this->fechaFin = new DateTimeImmutable('2024-12-31');
    }

    public function testHandleReturnsContractDTOsWhenContractsExist(): void
    {
        // Mock ContractDate para el primer contrato
        $contractDate1 = Mockery::mock(ContractDate::class);
        $contractDate1->shouldReceive('getFechaInicio')->andReturn($this->fechaInicio);
        $contractDate1->shouldReceive('getFechaFin')->andReturn($this->fechaFin);

        // Mock ContractDate para el segundo contrato
        $contractDate2 = Mockery::mock(ContractDate::class);
        $contractDate2->shouldReceive('getFechaInicio')->andReturn($this->fechaInicio);
        $contractDate2->shouldReceive('getFechaFin')->andReturn($this->fechaFin);

        $contract1 = Mockery::mock(Contract::class);
        $contract1->shouldReceive('getId')->andReturn('contract-1');
        $contract1->shouldReceive('getPacienteId')->andReturn($this->pacienteId);
        $contract1->shouldReceive('getServicioId')->andReturn('servicio-1');
        $contract1->shouldReceive('getFechaContrato')->andReturn($contractDate1);
        $contract1->shouldReceive('getEstado')->andReturn('ACTIVO');

        $contract2 = Mockery::mock(Contract::class);
        $contract2->shouldReceive('getId')->andReturn('contract-2');
        $contract2->shouldReceive('getPacienteId')->andReturn($this->pacienteId);
        $contract2->shouldReceive('getServicioId')->andReturn('servicio-2');
        $contract2->shouldReceive('getFechaContrato')->andReturn($contractDate2);
        $contract2->shouldReceive('getEstado')->andReturn('PENDIENTE');

        $this->repository->shouldReceive('findByPacienteId')
            ->once()
            ->with($this->pacienteId)
            ->andReturn([$contract1, $contract2]);

        $result = $this->handler->handle($this->query);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ContractDTO::class, $result);
        
        // Verificar el primer contrato
        $this->assertEquals('contract-1', $result[0]->id);
        $this->assertEquals($this->pacienteId, $result[0]->pacienteId);
        $this->assertEquals('servicio-1', $result[0]->servicioId);
        $this->assertEquals($this->fechaInicio, $result[0]->fechaInicio);
        $this->assertEquals($this->fechaFin, $result[0]->fechaFin);
        $this->assertEquals('ACTIVO', $result[0]->estado);

        // Verificar el segundo contrato
        $this->assertEquals('contract-2', $result[1]->id);
        $this->assertEquals($this->pacienteId, $result[1]->pacienteId);
        $this->assertEquals('servicio-2', $result[1]->servicioId);
        $this->assertEquals($this->fechaInicio, $result[1]->fechaInicio);
        $this->assertEquals($this->fechaFin, $result[1]->fechaFin);
        $this->assertEquals('PENDIENTE', $result[1]->estado);
    }

    public function testHandleReturnsEmptyArrayWhenNoContracts(): void
    {
        $this->repository->shouldReceive('findByPacienteId')
            ->once()
            ->with($this->pacienteId)
            ->andReturn([]);

        $result = $this->handler->handle($this->query);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testInvokeCallsHandle(): void
    {
        // Mock ContractDate
        $contractDate = Mockery::mock(ContractDate::class);
        $contractDate->shouldReceive('getFechaInicio')->andReturn($this->fechaInicio);
        $contractDate->shouldReceive('getFechaFin')->andReturn($this->fechaFin);

        $contract = Mockery::mock(Contract::class);
        $contract->shouldReceive('getId')->andReturn('contract-1');
        $contract->shouldReceive('getPacienteId')->andReturn($this->pacienteId);
        $contract->shouldReceive('getServicioId')->andReturn('servicio-1');
        $contract->shouldReceive('getFechaContrato')->andReturn($contractDate);
        $contract->shouldReceive('getEstado')->andReturn('ACTIVO');

        $this->repository->shouldReceive('findByPacienteId')
            ->once()
            ->with($this->pacienteId)
            ->andReturn([$contract]);

        $result = $this->handler->__invoke($this->query);

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(ContractDTO::class, $result);
        $this->assertEquals('contract-1', $result[0]->id);
    }
} 