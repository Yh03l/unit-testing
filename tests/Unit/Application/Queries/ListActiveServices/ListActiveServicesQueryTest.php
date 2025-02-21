<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListActiveServices;

use Commercial\Application\Queries\ListActiveServices\ListActiveServicesQuery;
use PHPUnit\Framework\TestCase;

class ListActiveServicesQueryTest extends TestCase
{
    private ListActiveServicesQuery $query;
    private string $catalogId;

    protected function setUp(): void
    {
        $this->catalogId = 'catalog-123';
        $this->query = new ListActiveServicesQuery($this->catalogId);
    }

    public function testGetCatalogId(): void
    {
        $this->assertEquals($this->catalogId, $this->query->getCatalogId());
    }
} 