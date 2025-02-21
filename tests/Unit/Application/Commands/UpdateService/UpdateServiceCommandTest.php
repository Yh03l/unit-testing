<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateService;

use Commercial\Application\Commands\UpdateService\UpdateServiceCommand;
use PHPUnit\Framework\TestCase;

class UpdateServiceCommandTest extends TestCase
{
    private UpdateServiceCommand $command;
    private string $id;
    private string $nombre;
    private string $descripcion;

    protected function setUp(): void
    {
        $this->id = 'service-123';
        $this->nombre = 'Updated Service Name';
        $this->descripcion = 'Updated Service Description';

        $this->command = new UpdateServiceCommand(
            $this->id,
            $this->nombre,
            $this->descripcion
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

    public function testGetDescripcion(): void
    {
        $this->assertEquals($this->descripcion, $this->command->getDescripcion());
    }
} 