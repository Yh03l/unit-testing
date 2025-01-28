<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListCatalogs;

final class ListCatalogsQuery
{
    public function __construct(
        private readonly ?string $estado = null
    ) {}

    public function getEstado(): ?string
    {
        return $this->estado;
    }
} 