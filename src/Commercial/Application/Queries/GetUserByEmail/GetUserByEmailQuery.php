<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetUserByEmail;

use Commercial\Domain\ValueObjects\Email;

final class GetUserByEmailQuery
{
    private readonly Email $email;

    public function __construct(string $email)
    {
        $this->email = Email::fromString($email);
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
} 