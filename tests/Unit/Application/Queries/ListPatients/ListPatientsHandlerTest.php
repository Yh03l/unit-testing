<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListPatients;

use Commercial\Application\Queries\ListPatients\ListPatientsQuery;
use Commercial\Application\Queries\ListPatients\ListPatientsHandler;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\ValueObjects\Email;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ListPatientsHandlerTest extends MockeryTestCase
{
	private ListPatientsHandler $handler;
	private UserRepository $repository;
	private ListPatientsQuery $query;

	protected function setUp(): void
	{
		$this->repository = Mockery::mock(UserRepository::class);
		$this->handler = new ListPatientsHandler($this->repository);
	}

	public function testInvokeReturnsPatientsList(): void
	{
		$limit = 10;
		$offset = 0;
		$this->query = new ListPatientsQuery($limit, $offset);

		$patient1 = Mockery::mock(Patient::class);
		$patient1->shouldReceive('getId')->andReturn('patient-1');
		$patient1->shouldReceive('getNombre')->andReturn('John');
		$patient1->shouldReceive('getApellido')->andReturn('Doe');
		$patient1->shouldReceive('getEmail')->andReturn(Email::fromString('john.doe@example.com'));
		$patient1->shouldReceive('getTipoUsuario')->andReturn('PACIENTE');
		$patient1->shouldReceive('getEstado')->andReturn('activo');
		$patient1
			->shouldReceive('getFechaNacimiento')
			->andReturn(new DateTimeImmutable('1990-01-01'));
		$patient1->shouldReceive('getGenero')->andReturn('M');
		$patient1->shouldReceive('getDireccion')->andReturn('Calle 123');
		$patient1->shouldReceive('getTelefono')->andReturn('123456789');

		$patient2 = Mockery::mock(Patient::class);
		$patient2->shouldReceive('getId')->andReturn('patient-2');
		$patient2->shouldReceive('getNombre')->andReturn('Jane');
		$patient2->shouldReceive('getApellido')->andReturn('Smith');
		$patient2
			->shouldReceive('getEmail')
			->andReturn(Email::fromString('jane.smith@example.com'));
		$patient2->shouldReceive('getTipoUsuario')->andReturn('PACIENTE');
		$patient2->shouldReceive('getEstado')->andReturn('activo');
		$patient2
			->shouldReceive('getFechaNacimiento')
			->andReturn(new DateTimeImmutable('1992-05-15'));
		$patient2->shouldReceive('getGenero')->andReturn('F');
		$patient2->shouldReceive('getDireccion')->andReturn('Avenida 456');
		$patient2->shouldReceive('getTelefono')->andReturn('987654321');

		$patients = [$patient1, $patient2];

		$this->repository
			->shouldReceive('findAllPatients')
			->once()
			->with($limit, $offset)
			->andReturn($patients);

		$result = $this->handler->__invoke($this->query);

		$this->assertIsArray($result);
		$this->assertCount(2, $result);

		$this->assertEquals('patient-1', $result[0]['id']);
		$this->assertEquals('John', $result[0]['nombre']);
		$this->assertEquals('Doe', $result[0]['apellido']);
		$this->assertEquals('john.doe@example.com', $result[0]['email']);

		$this->assertEquals('patient-2', $result[1]['id']);
		$this->assertEquals('Jane', $result[1]['nombre']);
		$this->assertEquals('Smith', $result[1]['apellido']);
		$this->assertEquals('jane.smith@example.com', $result[1]['email']);
	}

	public function testInvokeReturnsEmptyArrayWhenNoPatients(): void
	{
		$this->query = new ListPatientsQuery();

		$this->repository
			->shouldReceive('findAllPatients')
			->once()
			->with(null, null)
			->andReturn([]);

		$result = $this->handler->__invoke($this->query);

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}
}
