<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListContractsByPaciente;

use Commercial\Application\Queries\ListContractsByPaciente\ListContractsByPacienteQuery;
use PHPUnit\Framework\TestCase;

class ListContractsByPacienteQueryTest extends TestCase
{
    private ListContractsByPacienteQuery $query;
    private string $pacienteId;

    protected function setUp(): void
    {
        $this->pacienteId = 'paciente-123';
        $this->query = new ListContractsByPacienteQuery($this->pacienteId);
    }

    public function testGetPacienteId(): void
    {
        $this->assertEquals($this->pacienteId, $this->query->getPacienteId());
    }
} 