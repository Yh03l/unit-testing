<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events;

use Commercial\Domain\Events\ContractActivated;
use PHPUnit\Framework\TestCase;

class ContractActivatedTest extends TestCase
{
    private string $contractId;
    private ContractActivated $event;

    protected function setUp(): void
    {
        $this->contractId = 'test-contract-id';
        $this->event = new ContractActivated($this->contractId);
    }

    public function testGetContractId(): void
    {
        $this->assertEquals($this->contractId, $this->event->getContractId());
    }

    public function testGetOccurredOn(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }
} 