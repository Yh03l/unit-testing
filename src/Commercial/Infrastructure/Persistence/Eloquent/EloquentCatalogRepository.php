<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\Enums\TipoServicio;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class EloquentCatalogRepository implements CatalogRepository
{
	public function save(Catalog $catalog): void
	{
		$model = CatalogModel::updateOrCreate(
			['id' => $catalog->getId()],
			[
				'nombre' => $catalog->getNombre(),
				'estado' => $catalog->getEstado()->toString(),
			]
		);
	}

	public function findById(string $id): ?Catalog
	{
		$model = CatalogModel::with('services')->find($id);

		if (!$model) {
			return null;
		}

		return $this->toDomain($model);
	}

	public function findAll(): array
	{
		return CatalogModel::with('services')
			->get()
			->map(fn($model) => $this->toDomain($model))
			->all();
	}

	public function delete(string $id): void
	{
		CatalogModel::destroy($id);
	}

	private function toDomain(CatalogModel $model): Catalog
	{
		$catalog = Catalog::create(
			id: $model->id,
			nombre: $model->nombre,
			estado: ServiceStatus::fromString($model->estado)
		);

		// Mapear los servicios si existen
		if ($model->services) {
			foreach ($model->services as $serviceModel) {
				$service = $this->serviceToDomain($serviceModel);
				$catalog->addService($service);
			}
		}

		return $catalog;
	}

	private function serviceToDomain(ServiceModel $model): Service
	{
		$vigencia = $model->vigencia
			? new DateTimeImmutable($model->vigencia)
			: new DateTimeImmutable();
		$monto = $model->monto ?? 0.01; // Valor mínimo por defecto
		$moneda = $model->moneda ?? 'BOB'; // Moneda por defecto

		return new Service(
			id: $model->id,
			nombre: $model->nombre,
			descripcion: $model->descripcion,
			costo: new ServiceCost(monto: (float) $monto, moneda: $moneda, vigencia: $vigencia),
			tipo_servicio: TipoServicio::fromString($model->tipo_servicio_id),
			estado: ServiceStatus::fromString($model->estado),
			catalogo_id: $model->catalogo_id
		);
	}
}
