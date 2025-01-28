<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\User;

use Commercial\Domain\ValueObjects\Email;

final class Administrator extends User
{
    public function __construct(
        string $id,
        string $nombre,
        string $apellido,
        Email $email,
        string $estado,
        private string $cargo,
        private string $departamento
    ) {
        parent::__construct($id, $nombre, $apellido, $email, $estado);
    }

    public function getCargo(): string
    {
        return $this->cargo;
    }

    public function getDepartamento(): string
    {
        return $this->departamento;
    }

    public function updateCargo(string $cargo): void
    {
        $this->cargo = $cargo;
    }

    public function updateDepartamento(string $departamento): void
    {
        $this->departamento = $departamento;
    }

    public function getTipoUsuario(): string
    {
        return 'ADMINISTRADOR';
    }
} 