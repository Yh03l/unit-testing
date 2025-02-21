<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateCatalog;

use Commercial\Application\Commands\UpdateCatalog\UpdateCatalogCommand;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;

class UpdateCatalogCommandTest extends TestCase
{
    private UpdateCatalogCommand $command;
    private string $id;
    private string $nombre;
    private ServiceStatus $estado;

    protected function setUp(): void
    {
        $this->id = 'catalog-123';
        $this->nombre = 'Updated Catalog';
        $this->estado = ServiceStatus::ACTIVO;

        $this->command = new UpdateCatalogCommand(
            $this->id,
            $this->nombre,
            $this->estado
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals($this->id, $this->command->getId());
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