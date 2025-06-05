<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreatePlanAlimentario;

use Commercial\Domain\Aggregates\PlanAlimentario\PlanAlimentario;
use Commercial\Domain\Repositories\PlanAlimentarioRepository;
use Commercial\Domain\ValueObjects\PlanAlimentarioId;

final class CreatePlanAlimentarioCommandHandler
{
	public function __construct(private PlanAlimentarioRepository $repository) {}

	public function handle(CreatePlanAlimentarioCommand $command): void
	{
		$planAlimentario = PlanAlimentario::create(
			PlanAlimentarioId::fromString($command->id),
			$command->nombre,
			$command->tipo,
			$command->cantidadDias
		);

		$this->repository->save($planAlimentario);
	}
}
