<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\User;

use Commercial\Domain\ValueObjects\Email;

abstract class User
{
    public function __construct(
        private readonly string $id,
        private string $nombre,
        private string $apellido,
        private Email $email,
        private string $estado
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function updateInformation(string $nombre, string $apellido): void
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
    }

    public function updateEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function updateEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    abstract public function getTipoUsuario(): string;
} 