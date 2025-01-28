<?php

declare(strict_types=1);

namespace Commercial\Application\DTOs;

final class ServiceCostHistoryDTO
{
    public function __construct(
        private readonly float $monto,
        private readonly string $moneda,
        private readonly \DateTimeImmutable $vigencia
    ) {}

    public function getMonto(): float
    {
        return $this->monto;
    }

    public function getMoneda(): string
    {
        return $this->moneda;
    }

    public function getVigencia(): \DateTimeImmutable
    {
        return $this->vigencia;
    }

    public function toArray(): array
    {
        return [
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'vigencia' => $this->vigencia->format('Y-m-d H:i:s')
        ];
    }
}
