<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetPatientById;

use Commercial\Application\Queries\GetPatientById\GetPatientByIdQuery;
use PHPUnit\Framework\TestCase;

class GetPatientByIdQueryTest extends TestCase
{
	private GetPatientByIdQuery $query;
	private string $patientId;

	protected function setUp(): void
	{
		$this->patientId = 'patient-123';
		$this->query = new GetPatientByIdQuery($this->patientId);
	}

	public function testGetId(): void
	{
		$this->assertEquals($this->patientId, $this->query->id);
	}
}
