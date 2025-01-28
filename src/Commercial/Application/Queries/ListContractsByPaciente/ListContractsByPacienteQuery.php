<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListContractsByPaciente;

class ListContractsByPacienteQuery
{
    private string $pacienteId;

    public function __construct(string $pacienteId)
    {
        $this->pacienteId = $pacienteId;
    }

    public function getPacienteId(): string
    {
        return $this->pacienteId;
    }
} 