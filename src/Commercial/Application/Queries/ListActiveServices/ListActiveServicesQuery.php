<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListActiveServices;

final class ListActiveServicesQuery
{
    public function __construct(
        private readonly ?string $catalogId = null
    ) {}

    public function getCatalogId(): ?string
    {
        return $this->catalogId;
    }
}
