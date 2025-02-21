<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CancelContract;

use Commercial\Application\Commands\CancelContract\CancelContractCommand;
use PHPUnit\Framework\TestCase;

class CancelContractCommandTest extends TestCase
{
    private CancelContractCommand $command;
    private string $contractId;

    protected function setUp(): void
    {
        $this->contractId = 'contract-123';
        $this->command = new CancelContractCommand($this->contractId);
    }

    public function testGetContractId(): void
    {
        $this->assertEquals($this->contractId, $this->command->getContractId());
    }
} 