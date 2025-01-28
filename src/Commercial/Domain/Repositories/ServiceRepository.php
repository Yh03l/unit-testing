<?php

declare(strict_types=1);

namespace Commercial\Domain\Repositories;

use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceStatus;

interface ServiceRepository
{
    public function save(Service $service): void;
    public function findById(string $id): ?Service;
    public function findAll(): array;
    public function delete(string $id): void;
    public function findByStatus(ServiceStatus $status, ?string $catalogId = null): array;

    /**
     * Obtiene el historial de costos de un servicio
     * @param string $serviceId
     * @return array<ServiceCost>
     */
    public function getServiceCostHistory(string $serviceId): array;
} 