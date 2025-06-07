<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetPatientByEmail;

class GetPatientByEmailQuery
{
	public function __construct(public readonly string $email) {}
}
