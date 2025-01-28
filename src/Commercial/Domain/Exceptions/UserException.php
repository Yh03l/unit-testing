<?php

declare(strict_types=1);

namespace Commercial\Domain\Exceptions;

class UserException extends \DomainException
{
    public static function notFound(string $id): self
    {
        return new self("Usuario con ID {$id} no encontrado");
    }

    public static function emailAlreadyExists(string $email): self
    {
        return new self("Ya existe un usuario con el email {$email}");
    }

    public static function invalidEmail(string $email): self
    {
        return new self("El email {$email} no es válido");
    }

    public static function invalidState(string $currentState): self
    {
        return new self("Estado inválido del usuario: {$currentState}");
    }
} 