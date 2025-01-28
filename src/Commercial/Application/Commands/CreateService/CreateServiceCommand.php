<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateService;

use DateTimeImmutable;

final class CreateServiceCommand
{
    public function __construct(
        private readonly string $nombre,
        private readonly string $descripcion,
        private readonly float $monto,
        private readonly string $moneda,
        private readonly DateTimeImmutable $vigencia,
        private readonly string $tipo_servicio_id,
        private readonly string $catalogo_id
    ) {}

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
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

    public function getTipoServicioId(): string
    {
        return $this->tipo_servicio_id;
    }

    public function getCatalogoId(): string
    {
        return $this->catalogo_id;
    }
} 