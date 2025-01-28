<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateCatalog;

use Commercial\Domain\ValueObjects\ServiceStatus;

final class CreateCatalogCommand
{
    public function __construct(
        private readonly string $nombre,
        private readonly ServiceStatus $estado
    ) {}

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getEstado(): ServiceStatus
    {
        return $this->estado;
    }
} 