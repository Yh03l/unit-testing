<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Domain\ValueObjects\ServiceCost;
use Illuminate\Support\Collection;

class EloquentCatalogRepository implements CatalogRepository
{
    public function save(Catalog $catalog): void
    {
        $model = CatalogModel::updateOrCreate(
            ['id' => $catalog->getId()],
            [
                'nombre' => $catalog->getNombre(),
                'estado' => $catalog->getEstado()->toString()
            ]
        );
    }

    public function findById(string $id): ?Catalog
    {
        $model = CatalogModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findAll(): array
    {
        return CatalogModel::all()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function delete(string $id): void
    {
        CatalogModel::destroy($id);
    }

    private function toDomain(CatalogModel $model): Catalog
    {
        return Catalog::create(
            id: $model->id,
            nombre: $model->nombre,
            estado: ServiceStatus::fromString($model->estado)
        );
    }
} 