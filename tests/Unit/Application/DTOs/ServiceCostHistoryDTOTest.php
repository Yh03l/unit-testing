<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTOs;

use Commercial\Application\DTOs\ServiceCostHistoryDTO;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class ServiceCostHistoryDTOTest extends TestCase
{
    private float $monto;
    private string $moneda;
    private DateTimeImmutable $vigencia;
    private ServiceCostHistoryDTO $dto;

    protected function setUp(): void
    {
        $this->monto = 100.00;
        $this->moneda = 'BOB';
        $this->vigencia = new DateTimeImmutable('2024-01-01 10:00:00');
        $this->dto = new ServiceCostHistoryDTO(
            monto: $this->monto,
            moneda: $this->moneda,
            vigencia: $this->vigencia
        );
    }

    public function testGetMonto(): void
    {
        $this->assertEquals($this->monto, $this->dto->getMonto());
    }

    public function testGetMoneda(): void
    {
        $this->assertEquals($this->moneda, $this->dto->getMoneda());
    }

    public function testGetVigencia(): void
    {
        $this->assertEquals($this->vigencia, $this->dto->getVigencia());
    }

    public function testToArray(): void
    {
        $expected = [
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'vigencia' => $this->vigencia->format('Y-m-d H:i:s')
        ];

        $this->assertEquals($expected, $this->dto->toArray());
    }
} 