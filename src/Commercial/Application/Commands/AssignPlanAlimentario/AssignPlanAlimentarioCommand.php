<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\AssignPlanAlimentario;

class AssignPlanAlimentarioCommand
{
	public function __construct(
		private readonly string $contractId,
		private readonly string $planAlimentarioId
	) {}

	public function getContractId(): string
	{
		return $this->contractId;
	}

	public function getPlanAlimentarioId(): string
	{
		return $this->planAlimentarioId;
	}
}
