<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListCatalogs;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Application\DTOs\CatalogDTO;
use Commercial\Domain\ValueObjects\ServiceStatus;

final class ListCatalogsHandler
{
    public function __construct(
        private readonly CatalogRepository $catalogRepository
    ) {}

    public function __invoke(ListCatalogsQuery $query): array
    {
        $catalogs = $this->catalogRepository->findAll();

        if ($query->getEstado() !== null) {
            $estado = ServiceStatus::fromString($query->getEstado());
            $catalogs = array_filter(
                $catalogs,
                fn($catalog) => $catalog->getEstado() === $estado
            );
        }

        return array_map(
            fn($catalog) => CatalogDTO::fromEntity($catalog),
            $catalogs
        );
    }

    public function handle(ListCatalogsQuery $query): array
    {
        return $this->__invoke($query);
    }
}
