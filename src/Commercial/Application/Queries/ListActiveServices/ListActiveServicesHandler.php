<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListActiveServices;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Application\DTOs\ServiceDTO;

final class ListActiveServicesHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository
    ) {}

    public function __invoke(ListActiveServicesQuery $query): array
    {
        $services = $this->serviceRepository->findByStatus(
            status: ServiceStatus::ACTIVO,
            catalogId: $query->getCatalogId()
        );

        return array_map(
            fn($service) => ServiceDTO::fromEntity($service),
            $services
        );
    }

    public function handle(ListActiveServicesQuery $query): array
    {
        return $this->__invoke($query);
    }
}
