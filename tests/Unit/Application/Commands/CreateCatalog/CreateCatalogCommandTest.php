<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CreateCatalog;

use Commercial\Application\Commands\CreateCatalog\CreateCatalogCommand;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;

class CreateCatalogCommandTest extends TestCase
{
    private CreateCatalogCommand $command;
    private string $nombre;
    private ServiceStatus $estado;

    protected function setUp(): void
    {
        $this->nombre = 'Test Catalog';
        $this->estado = ServiceStatus::ACTIVO;
        $this->command = new CreateCatalogCommand($this->nombre, $this->estado);
    }

    public function testGetNombre(): void
    {
        $this->assertEquals($this->nombre, $this->command->getNombre());
    }

    public function testGetEstado(): void
    {
        $this->assertEquals($this->estado, $this->command->getEstado());
    }
} 