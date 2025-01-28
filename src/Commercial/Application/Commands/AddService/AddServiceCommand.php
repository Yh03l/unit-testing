<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\AddService;

class AddServiceCommand
{
    private string $catalogId;
    private string $nombre;
    private string $descripcion;
    private float $costo;
    private string $moneda;
    private \DateTimeImmutable $vigencia;
    private string $tipoServicioId;

    public function __construct(
        string $catalogId,
        string $nombre,
        string $descripcion,
        float $costo,
        string $moneda,
        \DateTimeImmutable $vigencia,
        string $tipoServicioId
    ) {
        $this->catalogId = $catalogId;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->costo = $costo;
        $this->moneda = $moneda;
        $this->vigencia = $vigencia;
        $this->tipoServicioId = $tipoServicioId;
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getCosto(): float
    {
        return $this->costo;
    }

    public function getMoneda(): string
    {
        return $this->moneda;
    }

    public function getVigencia(): \DateTimeImmutable
    {
        return $this->vigencia;
    }

    public function getTipoServicioId(): string
    {
        return $this->tipoServicioId;
    }
} 