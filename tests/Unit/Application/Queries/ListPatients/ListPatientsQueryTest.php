<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListPatients;

use Commercial\Application\Queries\ListPatients\ListPatientsQuery;
use PHPUnit\Framework\TestCase;

class ListPatientsQueryTest extends TestCase
{
	public function testConstructorWithDefaultValues(): void
	{
		$query = new ListPatientsQuery();

		$this->assertNull($query->limit);
		$this->assertNull($query->offset);
	}

	public function testConstructorWithCustomValues(): void
	{
		$limit = 10;
		$offset = 20;
		$query = new ListPatientsQuery($limit, $offset);

		$this->assertEquals($limit, $query->limit);
		$this->assertEquals($offset, $query->offset);
	}

	public function testConstructorWithOnlyLimit(): void
	{
		$limit = 15;
		$query = new ListPatientsQuery($limit);

		$this->assertEquals($limit, $query->limit);
		$this->assertNull($query->offset);
	}
}
