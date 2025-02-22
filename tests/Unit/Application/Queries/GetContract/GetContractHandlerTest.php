<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetContract;

use Commercial\Application\Queries\GetContract\GetContractHandler;
use Commercial\Application\Queries\GetContract\GetContractQuery;
use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\ValueObjects\ContractDate;
use Commercial\Application\DTOs\ContractDTO;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class GetContractHandlerTest extends MockeryTestCase
{
    private GetContractHandler $handler;
    private ContractRepository|MockInterface $repository;
    private string $contractId;
    private GetContractQuery $query;
    private DateTimeImmutable $fechaInicio;
    private DateTimeImmutable $fechaFin;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(ContractRepository::class);
        $this->handler = new GetContractHandler($this->repository);
        
        $this->contractId = 'contract-123';
        $this->query = new GetContractQuery($this->contractId);
        
        $this->fechaInicio = new DateTimeImmutable('2024-01-01');
        $this->fechaFin = new DateTimeImmutable('2024-12-31');
    }

    public function testHandleReturnsContractDTOWhenContractExists(): void
    {
        $contract = Mockery::mock(Contract::class);
        $contract->shouldReceive('getId')->andReturn($this->contractId);
        $contract->shouldReceive('getPacienteId')->andReturn('paciente-123');
        $contract->shouldReceive('getServicioId')->andReturn('servicio-456');
        $contract->shouldReceive('getFechaContrato->getFechaInicio')->andReturn($this->fechaInicio);
        $contract->shouldReceive('getFechaContrato->getFechaFin')->andReturn($this->fechaFin);
        $contract->shouldReceive('getEstado')->andReturn('ACTIVO');

        $this->repository->shouldReceive('findById')
            ->once()
            ->with($this->contractId)
            ->andReturn($contract);

        $result = $this->handler->handle($this->query);

        $this->assertInstanceOf(ContractDTO::class, $result);
        $this->assertEquals($this->contractId, $result->id);
        $this->assertEquals('paciente-123', $result->pacienteId);
        $this->assertEquals('servicio-456', $result->servicioId);
        $this->assertEquals($this->fechaInicio, $result->fechaInicio);
        $this->assertEquals($this->fechaFin, $result->fechaFin);
        $this->assertEquals('ACTIVO', $result->estado);
    }

    public function testHandleReturnsNullWhenContractNotFound(): void
    {
        $this->repository->shouldReceive('findById')
            ->once()
            ->with($this->contractId)
            ->andReturn(null);

        $result = $this->handler->handle($this->query);

        $this->assertNull($result);
    }

    public function testInvokeCallsHandle(): void
    {
        $contract = Mockery::mock(Contract::class);
        $contract->shouldReceive('getId')->andReturn($this->contractId);
        $contract->shouldReceive('getPacienteId')->andReturn('paciente-123');
        $contract->shouldReceive('getServicioId')->andReturn('servicio-456');
        $contract->shouldReceive('getFechaContrato->getFechaInicio')->andReturn($this->fechaInicio);
        $contract->shouldReceive('getFechaContrato->getFechaFin')->andReturn($this->fechaFin);
        $contract->shouldReceive('getEstado')->andReturn('ACTIVO');

        $this->repository->shouldReceive('findById')
            ->once()
            ->with($this->contractId)
            ->andReturn($contract);

        $result = $this->handler->handle($this->query);

        $this->assertInstanceOf(ContractDTO::class, $result);
        $this->assertEquals($this->contractId, $result->id);
    }
} 