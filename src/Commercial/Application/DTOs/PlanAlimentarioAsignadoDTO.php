<?php

declare(strict_types=1);

namespace Commercial\Application\DTOs;

class PlanAlimentarioAsignadoDTO
{
	public function __construct(
		public readonly string $idContrato,
		public readonly string $idPlanAlimentario
	) {}

	public static function fromArray(array $data): self
	{
		return new self($data['idContrato'], $data['idPlanAlimentario']);
	}
}
