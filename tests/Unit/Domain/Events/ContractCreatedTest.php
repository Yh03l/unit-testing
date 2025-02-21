<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events;

use Commercial\Domain\Events\ContractCreated;
use PHPUnit\Framework\TestCase;

class ContractCreatedTest extends TestCase
{
    private string $contractId;
    private string $pacienteId;
    private string $servicioId;
    private ContractCreated $event;

    protected function setUp(): void
    {
        $this->contractId = 'test-contract-id';
        $this->pacienteId = 'test-paciente-id';
        $this->servicioId = 'test-servicio-id';
        $this->event = new ContractCreated(
            $this->contractId,
            $this->pacienteId,
            $this->servicioId
        );
    }

    public function testGetContractId(): void
    {
        $this->assertEquals($this->contractId, $this->event->getContractId());
    }

    public function testGetPacienteId(): void
    {
        $this->assertEquals($this->pacienteId, $this->event->getPacienteId());
    }

    public function testGetServicioId(): void
    {
        $this->assertEquals($this->servicioId, $this->event->getServicioId());
    }

    public function testGetOccurredOn(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }
} 