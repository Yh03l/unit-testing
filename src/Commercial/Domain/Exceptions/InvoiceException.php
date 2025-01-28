<?php

declare(strict_types=1);

namespace Commercial\Domain\Exceptions;

final class InvoiceException extends \DomainException
{
    public static function invalidAmount(float $amount): self
    {
        return new self(sprintf('El monto de la factura debe ser mayor a 0. Monto proporcionado: %f', $amount));
    }

    public static function invalidDueDate(): self
    {
        return new self('La fecha de vencimiento debe ser posterior a la fecha de emisión');
    }

    public static function invalidPaymentState(string $currentState): self
    {
        return new self(sprintf('No se puede registrar el pago de una factura en estado %s', $currentState));
    }

    public static function cannotVoidPaidInvoice(): self
    {
        return new self('No se puede anular una factura que ya ha sido pagada');
    }

    public static function invoiceNotFound(string $id): self
    {
        return new self(sprintf('No se encontró la factura con ID %s', $id));
    }
} 