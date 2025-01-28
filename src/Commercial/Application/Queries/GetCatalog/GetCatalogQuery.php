<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetCatalog;

final class GetCatalogQuery
{
    public function __construct(
        public readonly string $id
    ) {}
} 