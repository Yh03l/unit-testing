<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class UserUpdated
{
    private string $userId;
    private \DateTimeImmutable $occurredOn;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
} 