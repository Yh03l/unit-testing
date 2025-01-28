<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateService;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;

final class UpdateServiceHandler
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly CatalogRepository $catalogRepository
    ) {}

    public function __invoke(UpdateServiceCommand $command): void
    {
        $service = $this->serviceRepository->findById($command->getId());

        if (!$service) {
            throw CatalogException::serviceNotFound($command->getId());
        }

        // Verificar que el catálogo está activo
        $catalog = $this->catalogRepository->findById($service->getCatalogoId());
        if (!$catalog || !$catalog->isActive()) {
            throw CatalogException::catalogNotActive($service->getCatalogoId());
        }

        if (!$service->canBeModified()) {
            throw CatalogException::serviceCannotBeModified($command->getId());
        }

        $service->update(
            name: $command->getNombre(),
            description: $command->getDescripcion()
        );

        $this->serviceRepository->save($service);
    }

    public function handle(UpdateServiceCommand $command): void
    {
        $this->__invoke($command);
    }
}
