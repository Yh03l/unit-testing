<?php

declare(strict_types=1);

namespace Commercial\Domain\Repositories;

use Commercial\Domain\Aggregates\Catalog\Catalog;

interface CatalogRepository
{
    public function save(Catalog $catalog): void;
    public function findById(string $id): ?Catalog;
    public function findAll(): array;
    public function delete(string $id): void;
} 