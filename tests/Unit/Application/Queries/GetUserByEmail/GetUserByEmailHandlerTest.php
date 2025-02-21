<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetUserByEmail;

use Commercial\Application\Queries\GetUserByEmail\GetUserByEmailHandler;
use Commercial\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class GetUserByEmailHandlerTest extends TestCase
{
    private UserRepository $repository;
    private GetUserByEmailHandler $handler;

    protected function setUp(): void
    {
        // Crear un mock del repositorio
        $this->repository = $this->createMock(UserRepository::class);
        // Crear el handler con el mock del repositorio
        $this->handler = new GetUserByEmailHandler($this->repository);
    }

    public function testHandleReturnsUserWhenExists(): void
    {
        // Arrange
        $email = Email::fromString('test@example.com');
        $expectedUser = $this->createMock(User::class);
        
        // Configurar el comportamiento esperado del mock
        $expectedUser->method('getNombre')->willReturn('John');
        $expectedUser->method('getApellido')->willReturn('Doe');
        $expectedUser->method('getEmail')->willReturn($email);

        // Configurar qué debe hacer el repositorio cuando se llame a findByEmail
        $this->repository
            ->expects($this->once()) // Esperamos que se llame exactamente una vez
            ->method('findByEmail')  // al método findByEmail
            ->with($email)          // con este email como parámetro
            ->willReturn($expectedUser); // y retornará nuestro usuario simulado

        $query = new GetUserByEmailQuery('test@example.com');

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('John', $result->nombre);
        $this->assertEquals('Doe', $result->apellido);
        $this->assertEquals('test@example.com', $result->email);
    }

    public function testHandleReturnsNullWhenUserNotFound(): void
    {
        // Arrange
        $email = Email::fromString('nonexistent@example.com');
        
        // El repositorio retornará null
        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $query = new GetUserByEmailQuery('nonexistent@example.com');

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertNull($result);
    }

    public function testHandleWithInvalidEmail(): void
    {
        // Arrange
        // No necesitamos configurar el mock del repositorio porque esperamos que falle antes

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        
        // Act
        $query = new GetUserByEmailQuery('invalid-email');
        $this->handler->handle($query);
    }

    public function testInvokeCallsHandle(): void
    {
        // Arrange
        $email = Email::fromString('test@example.com');
        $query = new GetUserByEmailQuery('test@example.com');
        $expectedUser = $this->createMock(User::class);
        
        $expectedUser->method('getNombre')->willReturn('John');
        $expectedUser->method('getApellido')->willReturn('Doe');
        $expectedUser->method('getEmail')->willReturn($email);

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($expectedUser);

        // Act
        $result = $this->handler->__invoke($query);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('John', $result->nombre);
        $this->assertEquals('Doe', $result->apellido);
        $this->assertEquals('test@example.com', $result->email);
    }
} 