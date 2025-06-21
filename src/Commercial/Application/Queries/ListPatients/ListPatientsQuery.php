<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListPatients;

class ListPatientsQuery
{
	public function __construct(
		public readonly ?int $limit = null,
		public readonly ?int $offset = null
	) {}
}
