<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\Catalog;

use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\ValueObjects\ServiceStatus;

class Service
{
    private string $id;
    private string $nombre;
    private string $descripcion;
    private ServiceCost $costo;
    private string $tipo_servicio_id;
    private ServiceStatus $estado;
    private string $catalogo_id;

    public function __construct(
        string $id,
        string $nombre,
        string $descripcion,
        ServiceCost $costo,
        string $tipo_servicio_id,
        ServiceStatus $estado,
        string $catalogo_id
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->costo = $costo;
        $this->tipo_servicio_id = $tipo_servicio_id;
        $this->estado = $estado;
        $this->catalogo_id = $catalogo_id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getCosto(): ServiceCost
    {
        return $this->costo;
    }

    public function getTipoServicioId(): string
    {
        return $this->tipo_servicio_id;
    }

    public function getEstado(): ServiceStatus
    {
        return $this->estado;
    }

    public function getCatalogoId(): string
    {
        return $this->catalogo_id;
    }

    public function update(string $name, string $description): void
    {
        if (!$this->canBeModified()) {
            throw new \DomainException('No se puede modificar el servicio en su estado actual');
        }
        $this->nombre = $name;
        $this->descripcion = $description;
    }

    public function updateCost(ServiceCost $cost): void
    {
        if (!$this->canUpdateCost()) {
            throw new \DomainException('No se puede actualizar el costo del servicio en su estado actual');
        }
        $this->costo = $cost;
    }

    public function updateEstado(ServiceStatus $estado): void
    {
        if (!$this->canUpdateStatus($estado)) {
            throw new \DomainException('No se puede actualizar el estado del servicio');
        }
        $this->estado = $estado;
    }

    public function canUpdateStatus(ServiceStatus $newStatus): bool
    {
        if ($this->estado === ServiceStatus::SUSPENDIDO) {
            return false;
        }

        // No se puede cambiar a ACTIVO si está SUSPENDIDO
        if ($newStatus === ServiceStatus::ACTIVO && $this->estado === ServiceStatus::SUSPENDIDO) {
            return false;
        }

        return true;
    }

    public function canBeModified(): bool
    {
        return $this->estado !== ServiceStatus::SUSPENDIDO;
    }

    public function canUpdateCost(): bool
    {
        return $this->estado === ServiceStatus::ACTIVO;
    }

    public function isActive(): bool
    {
        return $this->estado === ServiceStatus::ACTIVO;
    }

    public function __get(string $name)
    {
        return match($name) {
            'id' => $this->id,
            'name' => $this->nombre,
            'description' => $this->descripcion,
            'status' => $this->estado,
            'cost' => $this->costo,
            'catalogId' => $this->catalogo_id,
            default => throw new \InvalidArgumentException("Propiedad {$name} no existe")
        };
    }
} 