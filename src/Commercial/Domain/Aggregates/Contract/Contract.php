<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\Contract;

use Commercial\Domain\Events\ContractCreated;
use Commercial\Domain\Events\ContractActivated;
use Commercial\Domain\Events\ContractCancelled;
use Commercial\Domain\ValueObjects\ContractDate;

class Contract
{
    private string $id;
    private string $paciente_id;
    private string $servicio_id;
    private ContractDate $fecha_contrato;
    private string $estado;
    private array $events = [];

    private function __construct(
        string $id,
        string $paciente_id,
        string $servicio_id,
        ContractDate $fecha_contrato,
        string $estado
    ) {
        $this->id = $id;
        $this->paciente_id = $paciente_id;
        $this->servicio_id = $servicio_id;
        $this->fecha_contrato = $fecha_contrato;
        $this->estado = $estado;
    }

    public static function create(
        string $id,
        string $paciente_id,
        string $servicio_id,
        ContractDate $fecha_contrato
    ): self {
        $contract = new self(
            $id,
            $paciente_id,
            $servicio_id,
            $fecha_contrato,
            'PENDIENTE'
        );
        
        $contract->addEvent(new ContractCreated($id, $paciente_id, $servicio_id));
        return $contract;
    }

    public function activarContrato(): void
    {
        if ($this->estado !== 'PENDIENTE') {
            throw new \DomainException('Solo se pueden activar contratos pendientes');
        }

        $this->estado = 'ACTIVO';
        $this->addEvent(new ContractActivated($this->id));
    }

    public function cancelarContrato(): void
    {
        if ($this->estado === 'CANCELADO') {
            throw new \DomainException('El contrato ya está cancelado');
        }

        $this->estado = 'CANCELADO';
        $this->addEvent(new ContractCancelled($this->id));
    }

    public function generarFactura(): void
    {
        if ($this->estado !== 'ACTIVO') {
            throw new \DomainException('Solo se pueden generar facturas para contratos activos');
        }

        // Lógica para generar factura
        // Esto podría emitir un evento de factura generada
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPacienteId(): string
    {
        return $this->paciente_id;
    }

    public function getServicioId(): string
    {
        return $this->servicio_id;
    }

    public function getFechaContrato(): ContractDate
    {
        return $this->fecha_contrato;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    private function addEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }
} 