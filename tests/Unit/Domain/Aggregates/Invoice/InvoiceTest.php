<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregates\Invoice;

use Commercial\Domain\Aggregates\Invoice\Invoice;
use Commercial\Domain\Exceptions\InvoiceException;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class InvoiceTest extends TestCase
{
    private string $id;
    private string $contratoId;
    private string $pacienteId;
    private float $monto;
    private string $moneda;
    private DateTimeImmutable $fechaEmision;
    private DateTimeImmutable $fechaVencimiento;
    private Invoice $invoice;

    protected function setUp(): void
    {
        $this->id = 'invoice-123';
        $this->contratoId = 'contract-456';
        $this->pacienteId = 'patient-789';
        $this->monto = 100.00;
        $this->moneda = 'BOB';
        $this->fechaEmision = new DateTimeImmutable('now');
        $this->fechaVencimiento = new DateTimeImmutable('tomorrow');
        
        $this->invoice = Invoice::create(
            $this->id,
            $this->contratoId,
            $this->pacienteId,
            $this->monto,
            $this->moneda,
            $this->fechaEmision,
            $this->fechaVencimiento
        );
    }

    public function testCreateInvoice(): void
    {
        $this->assertEquals($this->id, $this->invoice->getId());
        $this->assertEquals($this->contratoId, $this->invoice->getContratoId());
        $this->assertEquals($this->pacienteId, $this->invoice->getPacienteId());
        $this->assertEquals($this->monto, $this->invoice->getMonto());
        $this->assertEquals($this->moneda, $this->invoice->getMoneda());
        $this->assertEquals($this->fechaEmision, $this->invoice->getFechaEmision());
        $this->assertEquals($this->fechaVencimiento, $this->invoice->getFechaVencimiento());
        $this->assertEquals('PENDIENTE', $this->invoice->getEstado());
        $this->assertNull($this->invoice->getFechaPago());
        $this->assertNull($this->invoice->getMetodoPago());
        $this->assertNull($this->invoice->getNumeroTransaccion());
    }

    public function testCreateInvoiceWithInvalidAmount(): void
    {
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('El monto de la factura debe ser mayor a 0. Monto proporcionado: -100.000000');
        
        Invoice::create(
            $this->id,
            $this->contratoId,
            $this->pacienteId,
            -100.00,
            $this->moneda,
            $this->fechaEmision,
            $this->fechaVencimiento
        );
    }

    public function testCreateInvoiceWithInvalidDueDate(): void
    {
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('La fecha de vencimiento debe ser posterior a la fecha de emisión');
        
        Invoice::create(
            $this->id,
            $this->contratoId,
            $this->pacienteId,
            $this->monto,
            $this->moneda,
            $this->fechaEmision,
            $this->fechaEmision // Misma fecha que emisión
        );
    }

    public function testRegistrarPago(): void
    {
        $fechaPago = new DateTimeImmutable();
        $metodoPago = 'TARJETA';
        $numeroTransaccion = 'TRX-123';
        
        $this->invoice->registrarPago($fechaPago, $metodoPago, $numeroTransaccion);
        
        $this->assertEquals('PAGADA', $this->invoice->getEstado());
        $this->assertEquals($fechaPago, $this->invoice->getFechaPago());
        $this->assertEquals($metodoPago, $this->invoice->getMetodoPago());
        $this->assertEquals($numeroTransaccion, $this->invoice->getNumeroTransaccion());
    }

    public function testRegistrarPagoEnFacturaNoValida(): void
    {
        $this->invoice->anular();
        
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('No se puede registrar el pago de una factura en estado ANULADA');
        
        $this->invoice->registrarPago(
            new DateTimeImmutable(),
            'TARJETA',
            'TRX-123'
        );
    }

    public function testAnularFactura(): void
    {
        $this->invoice->anular();
        $this->assertEquals('ANULADA', $this->invoice->getEstado());
    }

    public function testAnularFacturaPagada(): void
    {
        $this->invoice->registrarPago(
            new DateTimeImmutable(),
            'TARJETA',
            'TRX-123'
        );
        
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('No se puede anular una factura que ya ha sido pagada');
        
        $this->invoice->anular();
    }

    public function testEstaPagada(): void
    {
        $this->assertFalse($this->invoice->estaPagada());
        
        $this->invoice->registrarPago(
            new DateTimeImmutable(),
            'TARJETA',
            'TRX-123'
        );
        
        $this->assertTrue($this->invoice->estaPagada());
    }

    public function testEstaVencida(): void
    {
        // Crear una factura con fecha de vencimiento en el pasado
        $facturaVencida = Invoice::create(
            'invoice-vencida',
            $this->contratoId,
            $this->pacienteId,
            $this->monto,
            $this->moneda,
            new DateTimeImmutable('-2 days'),
            new DateTimeImmutable('-1 day')
        );
        
        $this->assertTrue($facturaVencida->estaVencida());
        $this->assertFalse($this->invoice->estaVencida()); // La factura del setUp no está vencida
    }
} 