<?php
declare(strict_types=1);

namespace Commercial\Domain\Repositories;

use Commercial\Domain\ValueObjects\PlanAlimentarioId;
use Commercial\Domain\Aggregates\PlanAlimentario\PlanAlimentario;

interface PlanAlimentarioRepository
{
	public function save(PlanAlimentario $planAlimentario): void;
	public function findById(PlanAlimentarioId $id): ?PlanAlimentario;
}
