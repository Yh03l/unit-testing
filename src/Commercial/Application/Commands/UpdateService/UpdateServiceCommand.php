<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateService;

final class UpdateServiceCommand
{
    public function __construct(
        private readonly string $id,
        private readonly string $nombre,
        private readonly string $descripcion
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }
}
