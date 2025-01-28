<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class CatalogCreated
{
    private string $catalogId;
    private \DateTimeImmutable $occurredOn;

    public function __construct(string $catalogId)
    {
        $this->catalogId = $catalogId;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
} 