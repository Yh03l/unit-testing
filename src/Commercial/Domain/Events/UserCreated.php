<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class UserCreated
{
    private string $userId;
    private string $email;
    private \DateTimeImmutable $occurredOn;

    public function __construct(string $userId, string $email)
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
} 