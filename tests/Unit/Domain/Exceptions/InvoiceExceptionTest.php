<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use Commercial\Domain\Exceptions\InvoiceException;
use PHPUnit\Framework\TestCase;

class InvoiceExceptionTest extends TestCase
{
    public function testInvalidAmount(): void
    {
        $amount = -100.00;
        $exception = InvoiceException::invalidAmount($amount);
        
        $this->assertInstanceOf(InvoiceException::class, $exception);
        $this->assertEquals(
            sprintf('El monto de la factura debe ser mayor a 0. Monto proporcionado: %f', $amount),
            $exception->getMessage()
        );
    }

    public function testInvalidDueDate(): void
    {
        $exception = InvoiceException::invalidDueDate();
        
        $this->assertInstanceOf(InvoiceException::class, $exception);
        $this->assertEquals(
            'La fecha de vencimiento debe ser posterior a la fecha de emisión',
            $exception->getMessage()
        );
    }

    public function testInvalidPaymentState(): void
    {
        $currentState = 'ANULADA';
        $exception = InvoiceException::invalidPaymentState($currentState);
        
        $this->assertInstanceOf(InvoiceException::class, $exception);
        $this->assertEquals(
            sprintf('No se puede registrar el pago de una factura en estado %s', $currentState),
            $exception->getMessage()
        );
    }

    public function testCannotVoidPaidInvoice(): void
    {
        $exception = InvoiceException::cannotVoidPaidInvoice();
        
        $this->assertInstanceOf(InvoiceException::class, $exception);
        $this->assertEquals(
            'No se puede anular una factura que ya ha sido pagada',
            $exception->getMessage()
        );
    }

    public function testInvoiceNotFound(): void
    {
        $id = 'invoice-123';
        $exception = InvoiceException::invoiceNotFound($id);
        
        $this->assertInstanceOf(InvoiceException::class, $exception);
        $this->assertEquals(
            sprintf('No se encontró la factura con ID %s', $id),
            $exception->getMessage()
        );
    }
} 