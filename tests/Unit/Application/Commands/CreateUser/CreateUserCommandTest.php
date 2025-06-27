<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CreateUser;

use Commercial\Application\Commands\CreateUser\CreateUserCommand;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
	private CreateUserCommand $command;
	private string $nombre;
	private string $apellido;
	private string $email;
	private string $tipoUsuarioId;

	protected function setUp(): void
	{
		$this->nombre = 'John';
		$this->apellido = 'Doe';
		$this->email = 'john.doe@example.com';
		$this->tipoUsuarioId = 'PACIENTE';

		$this->command = new CreateUserCommand(
			$this->nombre,
			$this->apellido,
			$this->email,
			$this->tipoUsuarioId
		);
	}

	public function testGetNombre(): void
	{
		$this->assertEquals($this->nombre, $this->command->getNombre());
	}

	public function testGetApellido(): void
	{
		$this->assertEquals($this->apellido, $this->command->getApellido());
	}

	public function testGetEmail(): void
	{
		$this->assertEquals($this->email, $this->command->getEmail());
	}

	public function testGetTipoUsuarioId(): void
	{
		$this->assertEquals($this->tipoUsuarioId, $this->command->getTipoUsuarioId());
	}
}
