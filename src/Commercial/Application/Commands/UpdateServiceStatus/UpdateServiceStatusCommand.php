<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateServiceStatus;

use Commercial\Domain\ValueObjects\ServiceStatus;

final class UpdateServiceStatusCommand
{
    public function __construct(
        private readonly string $id,
        private readonly ServiceStatus $estado
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getEstado(): ServiceStatus
    {
        return $this->estado;
    }
}
