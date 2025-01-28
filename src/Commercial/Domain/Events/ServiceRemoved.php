<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class ServiceRemoved
{
    private string $catalogId;
    private string $serviceId;
    private \DateTimeImmutable $occurredOn;

    public function __construct(string $catalogId, string $serviceId)
    {
        $this->catalogId = $catalogId;
        $this->serviceId = $serviceId;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
} 