<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Aggregates\PlanAlimentario\PlanAlimentario;
use Commercial\Domain\Repositories\PlanAlimentarioRepository;
use Commercial\Domain\ValueObjects\PlanAlimentarioId;

final class EloquentPlanAlimentarioRepository implements PlanAlimentarioRepository
{
	public function save(PlanAlimentario $planAlimentario): void
	{
		PlanAlimentarioModel::updateOrCreate(
			['id' => $planAlimentario->id()->value()],
			[
				'nombre' => $planAlimentario->nombre(),
				'tipo' => $planAlimentario->tipo(),
				'cantidad_dias' => $planAlimentario->cantidadDias(),
			]
		);
	}

	public function findById(PlanAlimentarioId $id): ?PlanAlimentario
	{
		$model = PlanAlimentarioModel::find($id->value());
		if (!$model) {
			return null;
		}

		return PlanAlimentario::create(
			PlanAlimentarioId::fromString($model->id),
			$model->nombre,
			$model->tipo,
			$model->cantidad_dias
		);
	}
}
