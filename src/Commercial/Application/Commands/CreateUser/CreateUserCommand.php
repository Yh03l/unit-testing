<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateUser;

class CreateUserCommand
{
	public function __construct(
		private readonly string $nombre,
		private readonly string $apellido,
		private readonly string $email,
		private readonly string $tipoUsuarioId,
		private readonly ?string $cargo = null,
		private readonly ?string $departamento = null,
		private readonly ?string $fechaNacimiento = null,
		private readonly ?string $genero = null,
		private readonly ?string $direccion = null,
		private readonly ?string $telefono = null
	) {}

	public function getNombre(): string
	{
		return $this->nombre;
	}

	public function getApellido(): string
	{
		return $this->apellido;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getTipoUsuarioId(): string
	{
		return $this->tipoUsuarioId;
	}

	public function getCargo(): ?string
	{
		return $this->cargo;
	}

	public function getDepartamento(): ?string
	{
		return $this->departamento;
	}

	public function getFechaNacimiento(): ?string
	{
		return $this->fechaNacimiento;
	}

	public function getGenero(): ?string
	{
		return $this->genero;
	}

	public function getDireccion(): ?string
	{
		return $this->direccion;
	}

	public function getTelefono(): ?string
	{
		return $this->telefono;
	}
}
