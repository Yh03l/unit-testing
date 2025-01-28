<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\DeleteCatalog;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;

final class DeleteCatalogHandler
{
    public function __construct(
        private readonly CatalogRepository $catalogRepository
    ) {}

    public function __invoke(DeleteCatalogCommand $command): void
    {
        $catalog = $this->catalogRepository->findById($command->id);

        if (!$catalog) {
            throw CatalogException::notFound($command->id);
        }

        $this->catalogRepository->delete($command->id);
    }

    public function handle(DeleteCatalogCommand $command): void
    {
        $this->__invoke($command);
    }
} 