<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetPatientById;

use Commercial\Application\Queries\GetPatientById\GetPatientByIdQuery;
use Commercial\Application\Queries\GetPatientById\GetPatientByIdHandler;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\ValueObjects\Email;
use Commercial\Application\DTOs\UserDTO;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class GetPatientByIdHandlerTest extends MockeryTestCase
{
	private GetPatientByIdHandler $handler;
	private UserRepository $repository;
	private string $patientId;
	private GetPatientByIdQuery $query;
	private Patient $patient;

	protected function setUp(): void
	{
		$this->patientId = 'patient-123';
		$this->repository = Mockery::mock(UserRepository::class);
		$this->handler = new GetPatientByIdHandler($this->repository);
		$this->query = new GetPatientByIdQuery($this->patientId);

		$this->patient = Mockery::mock(Patient::class);
		$this->patient->shouldReceive('getId')->andReturn($this->patientId);
		$this->patient->shouldReceive('getNombre')->andReturn('John');
		$this->patient->shouldReceive('getApellido')->andReturn('Doe');
		$this->patient
			->shouldReceive('getEmail')
			->andReturn(Email::fromString('john.doe@example.com'));
		$this->patient->shouldReceive('getTipoUsuario')->andReturn('PACIENTE');
		$this->patient->shouldReceive('getEstado')->andReturn('activo');
		$this->patient
			->shouldReceive('getFechaNacimiento')
			->andReturn(new DateTimeImmutable('1990-01-01'));
		$this->patient->shouldReceive('getGenero')->andReturn('M');
		$this->patient->shouldReceive('getDireccion')->andReturn('Calle 123');
		$this->patient->shouldReceive('getTelefono')->andReturn('123456789');
	}

	public function testInvokeReturnsPatientDataWhenPatientExists(): void
	{
		$this->repository
			->shouldReceive('findById')
			->once()
			->with($this->patientId)
			->andReturn($this->patient);

		$result = $this->handler->__invoke($this->query);

		$this->assertIsArray($result);
		$this->assertEquals($this->patientId, $result['id']);
		$this->assertEquals('John', $result['nombre']);
		$this->assertEquals('Doe', $result['apellido']);
		$this->assertEquals('john.doe@example.com', $result['email']);
		$this->assertEquals('PACIENTE', $result['tipo']);
		$this->assertEquals('activo', $result['estado']);
		$this->assertEquals('1990-01-01', $result['fecha_nacimiento']);
		$this->assertEquals('M', $result['genero']);
		$this->assertEquals('Calle 123', $result['direccion']);
		$this->assertEquals('123456789', $result['telefono']);
	}

	public function testInvokeReturnsNullWhenPatientNotFound(): void
	{
		$this->repository
			->shouldReceive('findById')
			->once()
			->with($this->patientId)
			->andReturn(null);

		$result = $this->handler->__invoke($this->query);

		$this->assertNull($result);
	}
}
