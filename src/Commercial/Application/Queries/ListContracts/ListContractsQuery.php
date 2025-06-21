<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListContracts;

class ListContractsQuery
{
	public function __construct(
		public readonly ?string $pacienteId = null,
		public readonly ?int $limit = null,
		public readonly ?int $offset = null
	) {}
}
