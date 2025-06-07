<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreatePatient;

class CreatePatientCommand
{
	public function __construct(
		public readonly string $nombre,
		public readonly string $apellido,
		public readonly string $email,
		public readonly string $fechaNacimiento,
		public readonly string $genero,
		public readonly ?string $direccion = null,
		public readonly ?string $telefono = null
	) {}
}
