<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\Invoice;

use DateTimeImmutable;
use Commercial\Domain\Exceptions\InvoiceException;

final class Invoice
{
    public const ESTADO_PENDIENTE = 'PENDIENTE';
    public const ESTADO_PAGADA = 'PAGADA';
    public const ESTADO_ANULADA = 'ANULADA';
    public const ESTADOS_VALIDOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_PAGADA,
        self::ESTADO_ANULADA
    ];

    private function __construct(
        private readonly string $id,
        private readonly string $contratoId,
        private readonly string $pacienteId,
        private readonly float $monto,
        private readonly string $moneda,
        private readonly DateTimeImmutable $fechaEmision,
        private readonly DateTimeImmutable $fechaVencimiento,
        private string $estado,
        private ?DateTimeImmutable $fechaPago = null,
        private ?string $metodoPago = null,
        private ?string $numeroTransaccion = null
    ) {}

    public static function create(
        string $id,
        string $contratoId,
        string $pacienteId,
        float $monto,
        string $moneda,
        DateTimeImmutable $fechaEmision,
        DateTimeImmutable $fechaVencimiento
    ): self {
        if ($monto <= 0) {
            throw InvoiceException::invalidAmount($monto);
        }

        if ($fechaVencimiento <= $fechaEmision) {
            throw InvoiceException::invalidDueDate();
        }

        return new self(
            $id,
            $contratoId,
            $pacienteId,
            $monto,
            $moneda,
            $fechaEmision,
            $fechaVencimiento,
            self::ESTADO_PENDIENTE
        );
    }

    public function registrarPago(
        DateTimeImmutable $fechaPago,
        string $metodoPago,
        string $numeroTransaccion
    ): void {
        if ($this->estado !== self::ESTADO_PENDIENTE) {
            throw InvoiceException::invalidPaymentState($this->estado);
        }

        $this->fechaPago = $fechaPago;
        $this->metodoPago = $metodoPago;
        $this->numeroTransaccion = $numeroTransaccion;
        $this->estado = self::ESTADO_PAGADA;
    }

    public function anular(): void
    {
        if ($this->estado === self::ESTADO_PAGADA) {
            throw InvoiceException::cannotVoidPaidInvoice();
        }

        $this->estado = self::ESTADO_ANULADA;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContratoId(): string
    {
        return $this->contratoId;
    }

    public function getPacienteId(): string
    {
        return $this->pacienteId;
    }

    public function getMonto(): float
    {
        return $this->monto;
    }

    public function getMoneda(): string
    {
        return $this->moneda;
    }

    public function getFechaEmision(): DateTimeImmutable
    {
        return $this->fechaEmision;
    }

    public function getFechaVencimiento(): DateTimeImmutable
    {
        return $this->fechaVencimiento;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getFechaPago(): ?DateTimeImmutable
    {
        return $this->fechaPago;
    }

    public function getMetodoPago(): ?string
    {
        return $this->metodoPago;
    }

    public function getNumeroTransaccion(): ?string
    {
        return $this->numeroTransaccion;
    }

    public function estaPagada(): bool
    {
        return $this->estado === self::ESTADO_PAGADA;
    }

    public function estaVencida(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE && 
               $this->fechaVencimiento < new DateTimeImmutable();
    }
} 