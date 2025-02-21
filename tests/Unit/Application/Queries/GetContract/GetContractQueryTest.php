<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetContract;

use Commercial\Application\Queries\GetContract\GetContractQuery;
use PHPUnit\Framework\TestCase;

class GetContractQueryTest extends TestCase
{
    private GetContractQuery $query;
    private string $contractId;

    protected function setUp(): void
    {
        $this->contractId = 'contract-123';
        $this->query = new GetContractQuery($this->contractId);
    }

    public function testGetContractId(): void
    {
        $this->assertEquals($this->contractId, $this->query->getContractId());
    }
} 