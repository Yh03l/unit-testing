<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\User;

use Commercial\Domain\ValueObjects\Email;
use DateTimeImmutable;

final class Patient extends User
{
    public function __construct(
        string $id,
        string $nombre,
        string $apellido,
        Email $email,
        string $estado,
        private DateTimeImmutable $fechaNacimiento,
        private string $genero,
        private ?string $direccion = null,
        private ?string $telefono = null
    ) {
        parent::__construct($id, $nombre, $apellido, $email, $estado);
    }

    public function getFechaNacimiento(): DateTimeImmutable
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

    public function updateContactInfo(?string $direccion, ?string $telefono): void
    {
        $this->direccion = $direccion;
        $this->telefono = $telefono;
    }

    public function getTipoUsuario(): string
    {
        return 'PACIENTE';
    }
} 