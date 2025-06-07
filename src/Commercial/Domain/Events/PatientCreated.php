<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class PatientCreated implements DomainEvent
{
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'cliente-creado';

	public function __construct(
		private readonly string $id,
		private readonly string $email,
		private readonly string $nombre,
		private readonly string $apellido,
		private readonly \DateTimeImmutable $fechaNacimiento,
		private readonly string $genero,
		private readonly ?string $direccion = null,
		private readonly ?string $telefono = null
	) {
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getNombre(): string
	{
		return $this->nombre;
	}

	public function getApellido(): string
	{
		return $this->apellido;
	}

	public function getFechaNacimiento(): \DateTimeImmutable
	{
		return $this->fechaNacimiento;
	}

	public function getGenero(): string
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

	public function getOccurredOn(): \DateTimeImmutable
	{
		return $this->occurredOn;
	}

	public function getExchangeName(): string
	{
		return self::EXCHANGE_NAME;
	}
}
