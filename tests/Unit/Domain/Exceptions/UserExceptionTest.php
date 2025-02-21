<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use Commercial\Domain\Exceptions\UserException;
use PHPUnit\Framework\TestCase;

class UserExceptionTest extends TestCase
{
    public function testNotFound(): void
    {
        $id = 'test-id';
        $exception = UserException::notFound($id);
        
        $this->assertInstanceOf(UserException::class, $exception);
        $this->assertEquals("Usuario con ID {$id} no encontrado", $exception->getMessage());
    }

    public function testEmailAlreadyExists(): void
    {
        $email = 'test@example.com';
        $exception = UserException::emailAlreadyExists($email);
        
        $this->assertInstanceOf(UserException::class, $exception);
        $this->assertEquals("Ya existe un usuario con el email {$email}", $exception->getMessage());
    }

    public function testInvalidEmail(): void
    {
        $email = 'invalid-email';
        $exception = UserException::invalidEmail($email);
        
        $this->assertInstanceOf(UserException::class, $exception);
        $this->assertEquals("El email {$email} no es válido", $exception->getMessage());
    }

    public function testInvalidState(): void
    {
        $currentState = 'INVALID';
        $exception = UserException::invalidState($currentState);
        
        $this->assertInstanceOf(UserException::class, $exception);
        $this->assertEquals("Estado inválido del usuario: {$currentState}", $exception->getMessage());
    }
} 