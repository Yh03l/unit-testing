<?php

declare(strict_types=1);

namespace Commercial\Domain\Exceptions;

class ContractException extends \DomainException
{
    public static function notFound(string $id): self
    {
        return new self("Contrato con ID {$id} no encontrado");
    }

    public static function invalidState(string $currentState, string $expectedState): self
    {
        return new self("Estado inválido del contrato. Estado actual: {$currentState}, Estado esperado: {$expectedState}");
    }

    public static function invalidDates(): self
    {
        return new self("Las fechas del contrato son inválidas");
    }
} 