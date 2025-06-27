<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Commercial\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class UserModelTest extends BaseModelTest
{
	private UserModel $model;

	protected function setUp(): void
	{
		parent::setUp();
		$this->model = new UserModel();
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
	}

	public function test_extends_eloquent_model(): void
	{
		$this->assertInstanceOf(Model::class, $this->model);
	}

	public function test_uses_correct_table(): void
	{
		$this->assertEquals('users', $this->model->getTable());
	}

	public function test_uses_correct_primary_key(): void
	{
		$this->assertEquals('id', $this->model->getKeyName());
	}

	public function test_fillable_attributes_are_correct(): void
	{
		$expectedFillable = [
			'id',
			'nombre',
			'apellido',
			'email',
			'password',
			'estado',
			'tipo_usuario',
		];

		$this->assertEquals($expectedFillable, $this->model->getFillable());
	}

	public function test_can_create_and_retrieve_user(): void
	{
		// Arrange
		$userData = [
			'nombre' => 'John',
			'apellido' => 'Doe',
			'email' => 'john@example.com',
			'tipo_usuario' => 'paciente',
			'estado' => 'activo',
		];

		// Act
		$user = UserModel::create($userData);
		$retrievedUser = UserModel::find($user->id);

		// Assert
		$this->assertNotNull($retrievedUser);
		$this->assertEquals($userData['nombre'], $retrievedUser->nombre);
		$this->assertEquals($userData['apellido'], $retrievedUser->apellido);
		$this->assertEquals($userData['email'], $retrievedUser->email);
		$this->assertEquals($userData['tipo_usuario'], $retrievedUser->tipo_usuario);
		$this->assertEquals($userData['estado'], $retrievedUser->estado);
	}

	public function test_uses_uuid_as_primary_key(): void
	{
		// Arrange
		$userData = [
			'nombre' => 'Jane',
			'apellido' => 'Doe',
			'email' => 'jane@example.com',
			'tipo_usuario' => 'paciente',
			'estado' => 'activo',
		];

		// Act
		$user = UserModel::create($userData);

		// Assert
		$this->assertIsString($user->id);
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
			$user->id
		);
	}

	public function test_timestamps_are_automatically_set(): void
	{
		// Arrange
		$userData = [
			'nombre' => 'Alice',
			'apellido' => 'Smith',
			'email' => 'alice@example.com',
			'tipo_usuario' => 'paciente',
			'estado' => 'activo',
		];

		// Act
		$user = UserModel::create($userData);

		// Assert
		$this->assertNotNull($user->created_at);
		$this->assertNotNull($user->updated_at);
	}
}
