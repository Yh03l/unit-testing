<?php

declare(strict_types=1);

namespace Commercial\Application\DTOs;

use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\Aggregates\User\Administrator;
use Commercial\Domain\Aggregates\User\Patient;
use DateTimeImmutable;

final class UserDTO
{
	private function __construct(
		public readonly string $id,
		public readonly string $nombre,
		public readonly string $apellido,
		public readonly string $email,
		public readonly string $tipo,
		public readonly string $estado,
		public readonly ?string $cargo = null,
		public readonly ?string $departamento = null,
		public readonly ?DateTimeImmutable $fechaNacimiento = null,
		public readonly ?string $genero = null,
		public readonly ?string $direccion = null,
		public readonly ?string $telefono = null
	) {}

	public static function fromEntity(User $user): self
	{
		if ($user instanceof Administrator) {
			return new self(
				id: $user->getId(),
				nombre: $user->getNombre(),
				apellido: $user->getApellido(),
				email: $user->getEmail()->toString(),
				tipo: $user->getTipoUsuario(),
				estado: $user->getEstado(),
				cargo: $user->getCargo(),
				departamento: $user->getDepartamento()
			);
		}

		if ($user instanceof Patient) {
			return new self(
				id: $user->getId(),
				nombre: $user->getNombre(),
				apellido: $user->getApellido(),
				email: $user->getEmail()->toString(),
				tipo: $user->getTipoUsuario(),
				estado: $user->getEstado(),
				fechaNacimiento: $user->getFechaNacimiento(),
				genero: $user->getGenero(),
				direccion: $user->getDireccion(),
				telefono: $user->getTelefono()
			);
		}

		return new self(
			id: $user->getId(),
			nombre: $user->getNombre(),
			apellido: $user->getApellido(),
			email: $user->getEmail()->toString(),
			tipo: $user->getTipoUsuario(),
			estado: $user->getEstado()
		);
	}

	public function toArray(): array
	{
		$data = [
			'id' => $this->id,
			'nombre' => $this->nombre,
			'apellido' => $this->apellido,
			'email' => $this->email,
			'tipo' => $this->tipo,
			'estado' => $this->estado,
		];

		if ($this->tipo === 'PACIENTE') {
			$data['fecha_nacimiento'] = $this->fechaNacimiento?->format('Y-m-d');
			$data['genero'] = $this->genero;
			$data['direccion'] = $this->direccion;
			$data['telefono'] = $this->telefono;
		} elseif ($this->tipo === 'ADMINISTRADOR') {
			$data['cargo'] = $this->cargo;
			$data['departamento'] = $this->departamento;
		}

		return $data;
	}
}
