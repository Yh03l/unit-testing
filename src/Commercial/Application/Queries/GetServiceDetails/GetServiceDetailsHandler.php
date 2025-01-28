<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetServiceDetails;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Application\DTOs\ServiceDTO;

final class GetServiceDetailsHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository
    ) {}

    public function __invoke(GetServiceDetailsQuery $query): ServiceDTO
    {
        return $this->handle($query);
    }

    public function handle(GetServiceDetailsQuery $query): ServiceDTO
    {
        $service = $this->serviceRepository->findById($query->serviceId);

        if (!$service) {
            throw CatalogException::serviceNotFound($query->serviceId);
        }

        return ServiceDTO::fromEntity($service);
    }
} 