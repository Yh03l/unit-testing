<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class ContractActivated
{
    private string $contractId;
    private \DateTimeImmutable $occurredOn;

    public function __construct(string $contractId)
    {
        $this->contractId = $contractId;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getContractId(): string
    {
        return $this->contractId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
} 