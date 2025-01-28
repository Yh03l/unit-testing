<?php

declare(strict_types=1);

namespace Commercial\Domain\ValueObjects;

enum ServiceStatus: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case SUSPENDIDO = 'suspendido';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'activo' => self::ACTIVO,
            'inactivo' => self::INACTIVO,
            'suspendido' => self::SUSPENDIDO,
            default => throw new \InvalidArgumentException('Estado de catálogo inválido')
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
