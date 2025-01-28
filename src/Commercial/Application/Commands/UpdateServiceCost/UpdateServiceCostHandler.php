<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateServiceCost;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceCost;

final class UpdateServiceCostHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository
    ) {}

    public function __invoke(UpdateServiceCostCommand $command): void
    {
        $service = $this->serviceRepository->findById($command->getId());

        if (!$service) {
            throw CatalogException::serviceNotFound($command->getId());
        }

        if (!$service->canUpdateCost()) {
            throw CatalogException::serviceCannotUpdateCost($command->getId());
        }

        $service->updateCost(new ServiceCost(
            monto: $command->getMonto(),
            moneda: $command->getMoneda(),
            vigencia: $command->getVigencia()
        ));

        $this->serviceRepository->save($service);
    }

    public function handle(UpdateServiceCostCommand $command): void
    {
        $this->__invoke($command);
    }
} 