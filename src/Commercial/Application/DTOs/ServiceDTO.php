<?php

declare(strict_types=1);

namespace Commercial\Application\DTOs;

use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Domain\ValueObjects\ServiceCost;

final class ServiceDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $nombre,
        public readonly string $descripcion,
        public readonly float $monto,
        public readonly string $moneda,
        public readonly string $tipo_servicio_id,
        public readonly string $estado,
        public readonly string $catalogo_id,
        public readonly \DateTimeImmutable $vigencia
    ) {}

    public static function fromEntity(Service $service): self
    {
        $costo = $service->getCosto();
        
        return new self(
            id: $service->getId(),
            nombre: $service->getNombre(),
            descripcion: $service->getDescripcion(),
            monto: $costo->getMonto(),
            moneda: $costo->getMoneda(),
            tipo_servicio_id: $service->getTipoServicioId(),
            estado: $service->getEstado()->toString(),
            catalogo_id: $service->getCatalogoId(),
            vigencia: $costo->getVigencia()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'tipo_servicio_id' => $this->tipo_servicio_id,
            'estado' => $this->estado,
            'catalogo_id' => $this->catalogo_id,
            'vigencia' => $this->vigencia->format('Y-m-d H:i:s')
        ];
    }
}
