<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Commercial\Infrastructure\Persistence\Eloquent\UserModel;
use Commercial\Infrastructure\Persistence\Eloquent\PatientModel;
use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\ValueObjects\Email;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class EloquentUserRepositoryTest extends MockeryTestCase
{
	private EloquentUserRepository $repository;
	private UserModel $userModel;

	protected function setUp(): void
	{
		$this->userModel = Mockery::mock(UserModel::class);
		$this->repository = new EloquentUserRepository($this->userModel);
	}

	public function testFindAllPatientsReturnsPatientsList(): void
	{
		$limit = 10;
		$offset = 0;

		$patientModel1 = Mockery::mock(UserModel::class);
		$patientModel1->id = 'patient-1';
		$patientModel1->nombre = 'John';
		$patientModel1->apellido = 'Doe';
		$patientModel1->email = 'john.doe@example.com';
		$patientModel1->estado = 'activo';
		$patientModel1->tipo_usuario = 'paciente';
		$patientModel1->patient = Mockery::mock(PatientModel::class);
		$patientModel1->patient->fecha_nacimiento = new \DateTime('1990-01-01');
		$patientModel1->patient->genero = 'M';
		$patientModel1->patient->direccion = 'Calle 123';
		$patientModel1->patient->telefono = '123456789';

		$patientModel2 = Mockery::mock(UserModel::class);
		$patientModel2->id = 'patient-2';
		$patientModel2->nombre = 'Jane';
		$patientModel2->apellido = 'Smith';
		$patientModel2->email = 'jane.smith@example.com';
		$patientModel2->estado = 'activo';
		$patientModel2->tipo_usuario = 'paciente';
		$patientModel2->patient = Mockery::mock(PatientModel::class);
		$patientModel2->patient->fecha_nacimiento = new \DateTime('1992-05-15');
		$patientModel2->patient->genero = 'F';
		$patientModel2->patient->direccion = 'Avenida 456';
		$patientModel2->patient->telefono = '987654321';

		$query = Mockery::mock();
		$query->shouldReceive('limit')->with($limit)->andReturnSelf();
		$query->shouldReceive('offset')->with($offset)->andReturnSelf();
		$query->shouldReceive('get')->andReturn(collect([$patientModel1, $patientModel2]));

		$this->userModel->shouldReceive('with')->with('patient')->andReturnSelf();
		$this->userModel
			->shouldReceive('where')
			->with('tipo_usuario', 'paciente')
			->andReturn($query);

		$result = $this->repository->findAllPatients($limit, $offset);

		$this->assertIsArray($result);
		$this->assertCount(2, $result);

		$this->assertInstanceOf(Patient::class, $result[0]);
		$this->assertEquals('patient-1', $result[0]->getId());
		$this->assertEquals('John', $result[0]->getNombre());
		$this->assertEquals('Doe', $result[0]->getApellido());

		$this->assertInstanceOf(Patient::class, $result[1]);
		$this->assertEquals('patient-2', $result[1]->getId());
		$this->assertEquals('Jane', $result[1]->getNombre());
		$this->assertEquals('Smith', $result[1]->getApellido());
	}

	public function testFindAllPatientsReturnsEmptyArrayWhenNoPatients(): void
	{
		$query = Mockery::mock();
		$query->shouldReceive('limit')->with(null)->andReturnSelf();
		$query->shouldReceive('offset')->with(null)->andReturnSelf();
		$query->shouldReceive('get')->andReturn(collect([]));

		$this->userModel->shouldReceive('with')->with('patient')->andReturnSelf();
		$this->userModel
			->shouldReceive('where')
			->with('tipo_usuario', 'paciente')
			->andReturn($query);

		$result = $this->repository->findAllPatients();

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}
}
