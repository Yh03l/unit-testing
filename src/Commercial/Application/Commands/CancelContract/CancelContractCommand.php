<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CancelContract;

class CancelContractCommand
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