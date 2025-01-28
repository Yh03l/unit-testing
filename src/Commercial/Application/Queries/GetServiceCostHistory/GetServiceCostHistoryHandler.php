<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetServiceCostHistory;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Application\DTOs\ServiceCostHistoryDTO;

final class GetServiceCostHistoryHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository
    ) {}

    public function __invoke(GetServiceCostHistoryQuery $query): array
    {
        $service = $this->serviceRepository->findById($query->getId());

        if (!$service) {
            throw CatalogException::serviceNotFound($query->getId());
        }

        $costHistory = $this->serviceRepository->getServiceCostHistory($query->getId());

        if (empty($costHistory)) {
            throw CatalogException::invalidCostHistory($query->getId());
        }

        return array_map(
            fn ($cost) => new ServiceCostHistoryDTO(
                monto: $cost->getMonto(),
                moneda: $cost->getMoneda(),
                vigencia: $cost->getVigencia()
            ),
            $costHistory
        );
    }

    public function handle(GetServiceCostHistoryQuery $query): array
    {
        return $this->__invoke($query);
    }
}
