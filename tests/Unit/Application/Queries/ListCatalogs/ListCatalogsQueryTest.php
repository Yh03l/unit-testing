<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListCatalogs;

use Commercial\Application\Queries\ListCatalogs\ListCatalogsQuery;
use PHPUnit\Framework\TestCase;

class ListCatalogsQueryTest extends TestCase
{
    private ListCatalogsQuery $query;

    protected function setUp(): void
    {
        $this->query = new ListCatalogsQuery();
    }

    public function testGetEstadoReturnsNullByDefault(): void
    {
        $this->assertNull($this->query->getEstado());
    }

    public function testGetEstadoReturnsProvidedValue(): void
    {
        $estado = 'ACTIVO';
        $queryWithEstado = new ListCatalogsQuery($estado);
        $this->assertEquals($estado, $queryWithEstado->getEstado());
    }

    public function testQueryCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ListCatalogsQuery::class, $this->query);
    }
} 