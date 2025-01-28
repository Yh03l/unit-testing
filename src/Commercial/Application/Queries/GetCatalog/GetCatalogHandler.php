<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetCatalog;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Application\DTOs\CatalogDTO;
use Commercial\Domain\Exceptions\CatalogException;

final class GetCatalogHandler
{
    public function __construct(
        private readonly CatalogRepository $catalogRepository
    ) {}

    public function __invoke(GetCatalogQuery $query): CatalogDTO
    {
        $catalog = $this->catalogRepository->findById($query->id);

        if (!$catalog) {
            throw CatalogException::notFound($query->id);
        }

        return CatalogDTO::fromEntity($catalog);
    }

    public function handle(GetCatalogQuery $query): CatalogDTO
    {
        return $this->__invoke($query);
    }
} 