<?php

declare(strict_types=1);

namespace Commercial\Domain\ValueObjects;

final class ServiceCost
{
    public function __construct(
        private readonly float $monto,
        private readonly string $moneda,
        private readonly \DateTimeImmutable $vigencia
    ) {
        $this->validateMonto($monto);
        $this->validateMoneda($moneda);
    }

    private function validateMonto(float $monto): void
    {
        if ($monto <= 0) {
            throw new \InvalidArgumentException('El monto debe ser mayor a 0');
        }
    }

    private function validateMoneda(string $moneda): void
    {
        if (!in_array($moneda, ['BOB', 'USD'])) {
            throw new \InvalidArgumentException('Moneda no válida. Use BOB o USD');
        }
    }

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

    public function equals(self $other): bool
    {
        return $this->monto === $other->monto &&
               $this->moneda === $other->moneda;
    }
} 