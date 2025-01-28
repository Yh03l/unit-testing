<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateUser;

class CreateUserCommand
{
    private string $nombre;
    private string $apellido;
    private string $email;
    private string $tipoUsuarioId;

    public function __construct(
        string $nombre,
        string $apellido,
        string $email,
        string $tipoUsuarioId
    ) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->tipoUsuarioId = $tipoUsuarioId;
    }

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
} 