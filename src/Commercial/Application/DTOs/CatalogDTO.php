<?php

declare(strict_types=1);

namespace Commercial\Application\DTOs;

use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\ValueObjects\ServiceStatus;

final class CatalogDTO
{
    /**
     * @param ServiceDTO[] $services
     */
    public function __construct(
        public readonly string $id,
        public readonly string $nombre,
        public readonly ServiceStatus $estado,
        public readonly array $services = []
    ) {}

    public static function fromEntity(Catalog $catalog): self
    {
        return new self(
            id: $catalog->getId(),
            nombre: $catalog->getNombre(),
            estado: $catalog->getEstado(),
            services: array_map(
                fn($service) => ServiceDTO::fromEntity($service),
                $catalog->getServices()
            )
        );
    }
} 