<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetPatientById;

class GetPatientByIdQuery
{
	public function __construct(public readonly string $id) {}
}
