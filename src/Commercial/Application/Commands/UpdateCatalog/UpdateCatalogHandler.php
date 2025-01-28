<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateCatalog;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;

final class UpdateCatalogHandler
{
    public function __construct(
        private readonly CatalogRepository $catalogRepository
    ) {}

    public function __invoke(UpdateCatalogCommand $command): void
    {
        $catalog = $this->catalogRepository->findById($command->getId());

        if (!$catalog) {
            throw CatalogException::notFound($command->getId());
        }

        $catalog->updateNombre($command->getNombre());

        if ($command->getEstado() !== null) {
            $catalog->updateEstado($command->getEstado());
        }

        $this->catalogRepository->save($catalog);
    }

    public function handle(UpdateCatalogCommand $command): void
    {
        $this->__invoke($command);
    }
} 