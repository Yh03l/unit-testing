<?php

declare(strict_types=1);

namespace Commercial\Domain\ValueObjects;

final class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureIsValidEmail($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    private function ensureIsValidEmail(string $value): void
    {
        // Eliminar espacios al inicio y final
        $value = trim($value);

        if (empty($value)) {
            throw new \InvalidArgumentException('El email no puede estar vacío');
        }

        // Validación básica con filter_var
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('<%s> no es un email válido', $value));
        }

        // Validaciones adicionales
        $parts = explode('@', $value);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException(sprintf('<%s> debe contener exactamente un símbolo @', $value));
        }

        [$local, $domain] = $parts;

        // Validar parte local
        if (strlen($local) > 64) {
            throw new \InvalidArgumentException('La parte local del email no puede exceder 64 caracteres');
        }

        // Validar dominio
        if (strlen($domain) > 255) {
            throw new \InvalidArgumentException('El dominio del email no puede exceder 255 caracteres');
        }

        if (str_starts_with($domain, '.') || str_ends_with($domain, '.')) {
            throw new \InvalidArgumentException('El dominio no puede empezar ni terminar con punto');
        }

        if (str_contains($domain, '..')) {
            throw new \InvalidArgumentException('El dominio no puede contener puntos consecutivos');
        }

        // Validar que no contenga caracteres Unicode
        if (mb_strlen($value) !== strlen($value)) {
            throw new \InvalidArgumentException('El email no puede contener caracteres Unicode');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
} 