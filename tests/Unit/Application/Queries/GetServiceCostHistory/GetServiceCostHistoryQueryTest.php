<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetServiceCostHistory;

use Commercial\Application\Queries\GetServiceCostHistory\GetServiceCostHistoryQuery;
use PHPUnit\Framework\TestCase;

class GetServiceCostHistoryQueryTest extends TestCase
{
    private GetServiceCostHistoryQuery $query;
    private string $serviceId;

    protected function setUp(): void
    {
        $this->serviceId = 'service-123';
        $this->query = new GetServiceCostHistoryQuery($this->serviceId);
    }

    public function testGetId(): void
    {
        $this->assertEquals($this->serviceId, $this->query->getId());
    }
}