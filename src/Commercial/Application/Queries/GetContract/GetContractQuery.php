<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetContract;

class GetContractQuery
{
    private string $contractId;

    public function __construct(string $contractId)
    {
        $this->contractId = $contractId;
    }

    public function getContractId(): string
    {
        return $this->contractId;
    }
} 