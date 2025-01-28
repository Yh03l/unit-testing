<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateServiceStatus;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Exceptions\CatalogException;

final class UpdateServiceStatusHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository
    ) {}

    public function __invoke(UpdateServiceStatusCommand $command): void
    {
        $service = $this->serviceRepository->findById($command->getId());

        if (!$service) {
            throw CatalogException::serviceNotFound($command->getId());
        }

        $service->updateEstado($command->getEstado());
        $this->serviceRepository->save($service);
    }

    public function handle(UpdateServiceStatusCommand $command): void
    {
        $this->__invoke($command);
    }
}
