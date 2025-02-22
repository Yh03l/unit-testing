<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetUserByEmail;

use Commercial\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use Commercial\Application\Queries\GetUserByEmail\GetUserByEmailHandler;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\ValueObjects\Email;
use Commercial\Application\DTOs\UserDTO;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class GetUserByEmailHandlerTest extends MockeryTestCase
{
    private GetUserByEmailHandler $handler;
    private UserRepository $repository;
    private string $email;
    private GetUserByEmailQuery $query;
    private Email $emailVO;

    protected function setUp(): void
    {
        $this->email = 'test@example.com';
        $this->emailVO = Email::fromString($this->email);
        $this->repository = Mockery::mock(UserRepository::class);
        $this->handler = new GetUserByEmailHandler($this->repository);
        $this->query = new GetUserByEmailQuery($this->email);
    }

    public function testHandleReturnsUserDTOWhenUserExists(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getId')->andReturn('user-123');
        $user->shouldReceive('getNombre')->andReturn('Test');
        $user->shouldReceive('getApellido')->andReturn('User');
        $user->shouldReceive('getEmail')->andReturn($this->emailVO);
        $user->shouldReceive('getTipoUsuario')->andReturn('ADMINISTRADOR');
        $user->shouldReceive('getEstado')->andReturn('activo');

        $this->repository->shouldReceive('findByEmail')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Email && (string)$arg === $this->email;
            }))
            ->andReturn($user);

        $result = $this->handler->handle($this->query);

        $this->assertInstanceOf(UserDTO::class, $result);
        $this->assertEquals('user-123', $result->id);
        $this->assertEquals('Test', $result->nombre);
        $this->assertEquals('User', $result->apellido);
        $this->assertEquals($this->email, $result->email);
        $this->assertEquals('ADMINISTRADOR', $result->tipo);
        $this->assertEquals('activo', $result->estado);
    }

    public function testHandleReturnsNullWhenUserNotFound(): void
    {
        $this->repository->shouldReceive('findByEmail')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Email && (string)$arg === $this->email;
            }))
            ->andReturn(null);

        $result = $this->handler->handle($this->query);

        $this->assertNull($result);
    }

    public function testInvokeCallsHandle(): void
    {
        $this->repository->shouldReceive('findByEmail')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof Email && (string)$arg === $this->email;
            }))
            ->andReturn(null);

        $result = $this->handler->__invoke($this->query);

        $this->assertNull($result);
    }
} 