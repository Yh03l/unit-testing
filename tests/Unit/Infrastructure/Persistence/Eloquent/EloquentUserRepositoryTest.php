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
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepositoryTest extends BaseModelTest
{
	use MockeryPHPUnitIntegration;

	private EloquentUserRepository $repository;
	private UserModel $userModel;

	protected function setUp(): void
	{
		parent::setUp();
		$this->userModel = Mockery::mock(UserModel::class);
		$this->repository = new EloquentUserRepository($this->userModel);
	}

	protected function createTables(): void
	{
		$this->schema->create('users', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('nombre');
			$table->string('apellido');
			$table->string('email')->unique();
			$table->string('password')->nullable();
			$table->string('estado');
			$table->string('tipo_usuario');
			$table->timestamp('email_verified_at')->nullable();
			$table->rememberToken();
			$table->timestamps();
			$table->softDeletes();
		});

		$this->schema->create('pacientes', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->uuid('user_id');
			$table->date('fecha_nacimiento');
			$table->string('genero');
			$table->text('direccion')->nullable();
			$table->string('telefono')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	public function testFindAllPatientsReturnsPatientsList(): void
	{
		$limit = 10;
		$offset = 0;

		$patientModel1 = new class (
			'patient-1',
			'John',
			'Doe',
			'john.doe@example.com',
			'activo',
			'paciente'
		) extends UserModel {
			public $id;
			public $nombre;
			public $apellido;
			public $email;
			public $estado;
			public $tipo_usuario;
			public $patient;

			public function __construct($id, $nombre, $apellido, $email, $estado, $tipo_usuario)
			{
				$this->id = $id;
				$this->nombre = $nombre;
				$this->apellido = $apellido;
				$this->email = $email;
				$this->estado = $estado;
				$this->tipo_usuario = $tipo_usuario;

				$this->patient = new class ($id) extends PatientModel {
					public $user_id;
					public $fecha_nacimiento;
					public $genero;
					public $direccion;
					public $telefono;

					public function __construct($userId)
					{
						$this->user_id = $userId;
						$this->fecha_nacimiento = new \DateTime('1990-01-01');
						$this->genero = 'M';
						$this->direccion = 'Calle 123';
						$this->telefono = '123456789';
					}
				};
			}
		};

		$patientModel2 = new class (
			'patient-2',
			'Jane',
			'Smith',
			'jane.smith@example.com',
			'activo',
			'paciente'
		) extends UserModel {
			public $id;
			public $nombre;
			public $apellido;
			public $email;
			public $estado;
			public $tipo_usuario;
			public $patient;

			public function __construct($id, $nombre, $apellido, $email, $estado, $tipo_usuario)
			{
				$this->id = $id;
				$this->nombre = $nombre;
				$this->apellido = $apellido;
				$this->email = $email;
				$this->estado = $estado;
				$this->tipo_usuario = $tipo_usuario;

				$this->patient = new class ($id) extends PatientModel {
					public $user_id;
					public $fecha_nacimiento;
					public $genero;
					public $direccion;
					public $telefono;

					public function __construct($userId)
					{
						$this->user_id = $userId;
						$this->fecha_nacimiento = new \DateTime('1992-05-15');
						$this->genero = 'F';
						$this->direccion = 'Avenida 456';
						$this->telefono = '987654321';
					}
				};
			}
		};

		// Crear datos reales en la base de datos
		$user1 = new UserModel([
			'id' => 'patient-1',
			'nombre' => 'John',
			'apellido' => 'Doe',
			'email' => 'john.doe@example.com',
			'estado' => 'activo',
			'tipo_usuario' => 'paciente',
		]);
		$user1->save();

		$user2 = new UserModel([
			'id' => 'patient-2',
			'nombre' => 'Jane',
			'apellido' => 'Smith',
			'email' => 'jane.smith@example.com',
			'estado' => 'activo',
			'tipo_usuario' => 'paciente',
		]);
		$user2->save();

		// Crear datos de pacientes
		$patient1 = new PatientModel([
			'id' => 'patient-1',
			'user_id' => 'patient-1',
			'fecha_nacimiento' => '1990-01-01',
			'genero' => 'M',
			'direccion' => 'Calle 123',
			'telefono' => '123456789',
		]);
		$patient1->save();

		$patient2 = new PatientModel([
			'id' => 'patient-2',
			'user_id' => 'patient-2',
			'fecha_nacimiento' => '1992-05-15',
			'genero' => 'F',
			'direccion' => 'Avenida 456',
			'telefono' => '987654321',
		]);
		$patient2->save();

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
		$query->shouldReceive('get')->andReturn(new Collection([]));

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
