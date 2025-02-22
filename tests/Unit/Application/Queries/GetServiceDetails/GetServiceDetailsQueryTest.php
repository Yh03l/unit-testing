<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetServiceDetails;

use Commercial\Application\Queries\GetServiceDetails\GetServiceDetailsQuery;
use PHPUnit\Framework\TestCase;

class GetServiceDetailsQueryTest extends TestCase
{
    private GetServiceDetailsQuery $query;
    private string $serviceId;

    protected function setUp(): void
    {
        $this->serviceId = 'service-123';
        $this->query = new GetServiceDetailsQuery($this->serviceId);
    }

    public function testGetServiceId(): void
    {
        $this->assertEquals($this->serviceId, $this->query->serviceId);
    }
} 