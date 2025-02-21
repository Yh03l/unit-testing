<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CreateContract;

use Commercial\Application\Commands\CreateContract\CreateContractCommand;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CreateContractCommandTest extends TestCase
{
    private CreateContractCommand $command;
    private string $pacienteId;
    private string $servicioId;
    private DateTimeImmutable $fechaInicio;
    private DateTimeImmutable $fechaFin;

    protected function setUp(): void
    {
        $this->pacienteId = 'paciente-123';
        $this->servicioId = 'servicio-456';
        $this->fechaInicio = new DateTimeImmutable('tomorrow');
        $this->fechaFin = new DateTimeImmutable('next week');

        $this->command = new CreateContractCommand(
            $this->pacienteId,
            $this->servicioId,
            $this->fechaInicio,
            $this->fechaFin
        );
    }

    public function testGetPacienteId(): void
    {
        $this->assertEquals($this->pacienteId, $this->command->getPacienteId());
    }

    public function testGetServicioId(): void
    {
        $this->assertEquals($this->servicioId, $this->command->getServicioId());
    }

    public function testGetFechaInicio(): void
    {
        $this->assertEquals($this->fechaInicio, $this->command->getFechaInicio());
    }

    public function testGetFechaFin(): void
    {
        $this->assertEquals($this->fechaFin, $this->command->getFechaFin());
    }
} 