<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Controllers;

use Commercial\Api\Controllers\UserController;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Application\Commands\CreateUser\CreateUserCommand;
use Commercial\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use Commercial\Api\Requests\CreateUserRequest;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Illuminate\Http\Response;
use Commercial\Application\Commands\CommandResult;

class UserControllerTest extends MockeryTestCase
{
	private UserController $controller;
	private CommandBus $commandBus;
	private QueryBus $queryBus;

	protected function setUp(): void
	{
		parent::setUp();
		$this->commandBus = Mockery::mock(CommandBus::class);
		$this->queryBus = Mockery::mock(QueryBus::class);
		$this->controller = new UserController($this->commandBus, $this->queryBus);
	}

	public function testCreateUser(): void
	{
		$request = Mockery::mock(CreateUserRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('John');
		$request->shouldReceive('validated')->with('apellido')->andReturn('Doe');
		$request->shouldReceive('validated')->with('email')->andReturn('john.doe@example.com');
		$request->shouldReceive('validated')->with('tipo_usuario_id')->andReturn('PACIENTE');

		$commandResult = Mockery::mock(CommandResult::class);
		$commandResult->shouldReceive('isSuccess')->andReturn(true);
		$commandResult->shouldReceive('getId')->andReturn('user-123');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateUserCommand::class))
			->andReturn($commandResult);

		$userData = [
			'id' => 'user-123',
			'nombre' => 'John',
			'apellido' => 'Doe',
			'email' => 'john.doe@example.com',
			'tipo' => 'PACIENTE',
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetUserByEmailQuery::class))
			->andReturn($userData);

		$response = $this->controller->create($request);

		$this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
		$responseData = json_decode($response->getContent(), true);
		$this->assertEquals($userData, $responseData['data']);
		$this->assertEquals('Usuario creado exitosamente', $responseData['message']);
	}

	public function testCreateUserHandlesDomainException(): void
	{
		$request = Mockery::mock(CreateUserRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('John');
		$request->shouldReceive('validated')->with('apellido')->andReturn('Doe');
		$request->shouldReceive('validated')->with('email')->andReturn('john.doe@example.com');
		$request->shouldReceive('validated')->with('tipo_usuario_id')->andReturn('PACIENTE');

		$commandResult = Mockery::mock(CommandResult::class);
		$commandResult->shouldReceive('isSuccess')->andReturn(false);
		$commandResult->shouldReceive('getMessage')->andReturn('Error de dominio');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateUserCommand::class))
			->andReturn($commandResult);

		$response = $this->controller->create($request);

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertEquals(
			'Error de dominio',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testGetUserByEmail(): void
	{
		$email = 'john.doe@example.com';
		$userData = [
			'id' => 'user-123',
			'nombre' => 'John',
			'apellido' => 'Doe',
			'email' => $email,
			'tipo' => 'PACIENTE',
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetUserByEmailQuery::class))
			->once()
			->andReturn($userData);

		$response = $this->controller->getByEmail($email);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$responseData = json_decode($response->getContent(), true);
		$this->assertEquals($userData, $responseData['data']);
	}

	public function testGetUserByEmailNotFound(): void
	{
		$email = 'nonexistent@example.com';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetUserByEmailQuery::class))
			->once()
			->andReturn(null);

		$response = $this->controller->getByEmail($email);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Usuario no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testGetUserByEmailHandlesException(): void
	{
		$email = 'john.doe@example.com';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetUserByEmailQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->getByEmail($email);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals('Error interno', json_decode($response->getContent(), true)['error']);
	}
}
