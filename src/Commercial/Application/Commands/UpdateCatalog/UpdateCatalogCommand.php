<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateCatalog;

use Commercial\Domain\ValueObjects\ServiceStatus;

final class UpdateCatalogCommand
{
    public function __construct(
        private readonly string $id,
        private readonly string $nombre,
        private readonly ?ServiceStatus $estado
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getEstado(): ?ServiceStatus
    {
        return $this->estado;
    }
} 