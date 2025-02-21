<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\UpdateServiceCost;

use Commercial\Application\Commands\UpdateServiceCost\UpdateServiceCostCommand;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class UpdateServiceCostCommandTest extends TestCase
{
    private UpdateServiceCostCommand $command;
    private string $id;
    private float $monto;
    private string $moneda;
    private DateTimeImmutable $vigencia;

    protected function setUp(): void
    {
        $this->id = 'service-123';
        $this->monto = 150.00;
        $this->moneda = 'BOB';
        $this->vigencia = new DateTimeImmutable('2024-12-31');

        $this->command = new UpdateServiceCostCommand(
            $this->id,
            $this->monto,
            $this->moneda,
            $this->vigencia
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals($this->id, $this->command->getId());
    }

    public function testGetMonto(): void
    {
        $this->assertEquals($this->monto, $this->command->getMonto());
    }

    public function testGetMoneda(): void
    {
        $this->assertEquals($this->moneda, $this->command->getMoneda());
    }

    public function testGetVigencia(): void
    {
        $this->assertEquals($this->vigencia, $this->command->getVigencia());
    }
} 