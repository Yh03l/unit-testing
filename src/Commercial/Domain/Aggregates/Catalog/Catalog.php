<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\Catalog;

use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\Events\ServiceAdded;
use Commercial\Domain\Events\ServiceRemoved;

class Catalog
{
    private array $services = [];
    private array $events = [];

    private function __construct(
        private string $id,
        private string $nombre,
        private ServiceStatus $estado
    ) {}

    public static function create(string $id, string $nombre, ServiceStatus $estado): self
    {
        return new self($id, $nombre, $estado);
    }

    public function addService(Service $service): void
    {
        if (isset($this->services[$service->getId()])) {
            throw CatalogException::serviceAlreadyExists($service->getId());
        }
        $this->services[$service->getId()] = $service;
        $this->services[] = $service;
        $this->addEvent(new ServiceAdded($this->id, $service->getId()));
    }

    public function removeService(string $serviceId): void
    {
        if (!isset($this->services[$serviceId])) {
            throw CatalogException::serviceNotFound($serviceId);
        }
        unset($this->services[$serviceId]);
    }

    public function updateService(Service $service): void
    {
        if (!isset($this->services[$service->getId()])) {
            throw CatalogException::serviceNotFound($service->getId());
        }
        $this->services[$service->getId()] = $service;
    }

    public function getService(string $serviceId): ?Service
    {
        return $this->services[$serviceId] ?? null;
    }

    public function getServices(): array
    {
        return array_values($this->services);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getEstado(): ServiceStatus
    {
        return $this->estado;
    }

    public function updateEstado(ServiceStatus $estado): void
    {
        $this->estado = $estado;
    }

    public function updateNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function isActive(): bool
    {
        return $this->estado === ServiceStatus::ACTIVO;
    }

    public function isInactive(): bool
    {
        return $this->estado === ServiceStatus::INACTIVO;
    }

    public function isSuspended(): bool
    {
        return $this->estado === ServiceStatus::SUSPENDIDO;
    }

    public function isActiveOrInactive(): bool
    {
        return $this->isActive() || $this->isInactive();
    }

    public function isActiveOrSuspended(): bool
    {
        return $this->isActive() || $this->isSuspended();
    }

    public function isInactiveOrSuspended(): bool
    {
        return $this->isInactive() || $this->isSuspended();
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }
} 