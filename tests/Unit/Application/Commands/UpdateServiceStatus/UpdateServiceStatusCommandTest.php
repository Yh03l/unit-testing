<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateServiceStatus;

use Commercial\Application\Commands\UpdateServiceStatus\UpdateServiceStatusCommand;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;

class UpdateServiceStatusCommandTest extends TestCase
{
    private UpdateServiceStatusCommand $command;
    private string $id;
    private ServiceStatus $estado;

    protected function setUp(): void
    {
        $this->id = 'service-123';
        $this->estado = ServiceStatus::INACTIVO;

        $this->command = new UpdateServiceStatusCommand(
            $this->id,
            $this->estado
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals($this->id, $this->command->getId());
    }

    public function testGetEstado(): void
    {
        $this->assertEquals($this->estado, $this->command->getEstado());
    }
} 