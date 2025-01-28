<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Http\Request;

class CreateContractRequest extends Request
{
    private string $pacienteId;
    private string $servicioId;
    private \DateTimeImmutable $fechaInicio;
    private ?\DateTimeImmutable $fechaFin;

    public function rules(): array
    {
        return [
            'paciente_id' => 'required|uuid',
            'servicio_id' => 'required|uuid',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio'
        ];
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

    protected function prepareForValidation(): void
    {
        $this->pacienteId = $this->input('paciente_id');
        $this->servicioId = $this->input('servicio_id');
        $this->fechaInicio = new \DateTimeImmutable($this->input('fecha_inicio'));
        $fechaFin = $this->input('fecha_fin');
        $this->fechaFin = $fechaFin ? new \DateTimeImmutable($fechaFin) : null;
    }
} 