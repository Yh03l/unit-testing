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
        $request->shouldReceive('getNombre')->andReturn('John');
        $request->shouldReceive('getApellido')->andReturn('Doe');
        $request->shouldReceive('getEmail')->andReturn('john.doe@example.com');
        $request->shouldReceive('getTipoUsuarioId')->andReturn('tipo-123');

        $this->commandBus->shouldReceive('dispatch')
                        ->with(Mockery::type(CreateUserCommand::class))
                        ->once();

        $response = $this->controller->create($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals(
            'Usuario creado exitosamente',
            json_decode($response->getContent(), true)['message']
        );
    }

    public function testCreateUserHandlesDomainException(): void
    {
        $request = Mockery::mock(CreateUserRequest::class);
        $request->shouldReceive('getNombre')->andReturn('John');
        $request->shouldReceive('getApellido')->andReturn('Doe');
        $request->shouldReceive('getEmail')->andReturn('john.doe@example.com');
        $request->shouldReceive('getTipoUsuarioId')->andReturn('tipo-123');

        $this->commandBus->shouldReceive('dispatch')
                        ->with(Mockery::type(CreateUserCommand::class))
                        ->once()
                        ->andThrow(new \DomainException('Error al crear el usuario'));

        $response = $this->controller->create($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(
            'Error al crear el usuario',
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
            'tipo' => 'PACIENTE'
        ];

        $this->queryBus->shouldReceive('ask')
                      ->with(Mockery::type(GetUserByEmailQuery::class))
                      ->once()
                      ->andReturn($userData);

        $response = $this->controller->getByEmail($email);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($userData, json_decode($response->getContent(), true));
    }

    public function testGetUserByEmailNotFound(): void
    {
        $email = 'nonexistent@example.com';

        $this->queryBus->shouldReceive('ask')
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

        $this->queryBus->shouldReceive('ask')
                      ->with(Mockery::type(GetUserByEmailQuery::class))
                      ->once()
                      ->andThrow(new \Exception('Error interno'));

        $response = $this->controller->getByEmail($email);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(
            'Error interno',
            json_decode($response->getContent(), true)['error']
        );
    }
} 