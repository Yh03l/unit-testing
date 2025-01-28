<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateServiceCost;

use DateTimeImmutable;

final class UpdateServiceCostCommand
{
    public function __construct(
        private readonly string $id,
        private readonly float $monto,
        private readonly string $moneda,
        private readonly DateTimeImmutable $vigencia
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getMonto(): float
    {
        return $this->monto;
    }

    public function getMoneda(): string
    {
        return $this->moneda;
    }

    public function getVigencia(): DateTimeImmutable
    {
        return $this->vigencia;
    }
} 