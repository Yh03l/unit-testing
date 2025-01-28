<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateContract;

class CreateContractCommand
{
    private string $pacienteId;
    private string $servicioId;
    private \DateTimeImmutable $fechaInicio;
    private ?\DateTimeImmutable $fechaFin;

    public function __construct(
        string $pacienteId,
        string $servicioId,
        \DateTimeImmutable $fechaInicio,
        ?\DateTimeImmutable $fechaFin = null
    ) {
        $this->pacienteId = $pacienteId;
        $this->servicioId = $servicioId;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function getPacienteId(): string
    {
        return $this->pacienteId;
    }

    public function getServicioId(): string
    {
        return $this->servicioId;
    }

    public function getFechaInicio(): \DateTimeImmutable
    {
        return $this->fechaInicio;
    }

    public function getFechaFin(): ?\DateTimeImmutable
    {
        return $this->fechaFin;
    }
} 