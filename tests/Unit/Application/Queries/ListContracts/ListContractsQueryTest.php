<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListContracts;

use Commercial\Application\Queries\ListContracts\ListContractsQuery;
use PHPUnit\Framework\TestCase;

class ListContractsQueryTest extends TestCase
{
	public function testConstructorWithDefaultValues(): void
	{
		$query = new ListContractsQuery();

		$this->assertNull($query->pacienteId);
		$this->assertNull($query->limit);
		$this->assertNull($query->offset);
	}

	public function testConstructorWithPacienteId(): void
	{
		$pacienteId = 'paciente-123';
		$query = new ListContractsQuery($pacienteId);

		$this->assertEquals($pacienteId, $query->pacienteId);
		$this->assertNull($query->limit);
		$this->assertNull($query->offset);
	}

	public function testConstructorWithAllParameters(): void
	{
		$pacienteId = 'paciente-123';
		$limit = 10;
		$offset = 20;
		$query = new ListContractsQuery($pacienteId, $limit, $offset);

		$this->assertEquals($pacienteId, $query->pacienteId);
		$this->assertEquals($limit, $query->limit);
		$this->assertEquals($offset, $query->offset);
	}

	public function testConstructorWithPaginationOnly(): void
	{
		$limit = 15;
		$offset = 5;
		$query = new ListContractsQuery(null, $limit, $offset);

		$this->assertNull($query->pacienteId);
		$this->assertEquals($limit, $query->limit);
		$this->assertEquals($offset, $query->offset);
	}
}
