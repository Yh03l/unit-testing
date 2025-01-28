<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class ContractCreated
{
    private string $contractId;
    private string $pacienteId;
    private string $servicioId;
    private \DateTimeImmutable $occurredOn;

    public function __construct(string $contractId, string $pacienteId, string $servicioId)
    {
        $this->contractId = $contractId;
        $this->pacienteId = $pacienteId;
        $this->servicioId = $servicioId;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getContractId(): string
    {
        return $this->contractId;
    }

    public function getPacienteId(): string
    {
        return $this->pacienteId;
    }

    public function getServicioId(): string
    {
        return $this->servicioId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
} 