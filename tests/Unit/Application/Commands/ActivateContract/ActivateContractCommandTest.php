<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\ActivateContract;

use Commercial\Application\Commands\ActivateContract\ActivateContractCommand;
use PHPUnit\Framework\TestCase;

class ActivateContractCommandTest extends TestCase
{
    private ActivateContractCommand $command;
    private string $contractId;

    protected function setUp(): void
    {
        $this->contractId = 'contract-123';
        $this->command = new ActivateContractCommand($this->contractId);
    }

    public function testGetContractId(): void
    {
        $this->assertEquals($this->contractId, $this->command->getContractId());
    }
} 