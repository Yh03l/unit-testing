<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Commercial\Domain\ValueObjects\ServiceCost;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use DateTimeImmutable;

class ServiceCostTest extends TestCase
{
    private float $monto;
    private string $moneda;
    private DateTimeImmutable $vigencia;
    private ServiceCost $serviceCost;

    protected function setUp(): void
    {
        $this->monto = 100.00;
        $this->moneda = 'BOB';
        $this->vigencia = new DateTimeImmutable('2024-01-01');
        $this->serviceCost = new ServiceCost(
            monto: $this->monto,
            moneda: $this->moneda,
            vigencia: $this->vigencia
        );
    }

    public function testCreateValidServiceCost(): void
    {
        $this->assertEquals($this->monto, $this->serviceCost->getMonto());
        $this->assertEquals($this->moneda, $this->serviceCost->getMoneda());
        $this->assertEquals($this->vigencia, $this->serviceCost->getVigencia());
    }

    public function testThrowsExceptionForInvalidMonto(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El monto debe ser mayor a 0');

        new ServiceCost(
            monto: 0,
            moneda: $this->moneda,
            vigencia: $this->vigencia
        );
    }

    public function testThrowsExceptionForInvalidMoneda(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Moneda no válida. Use BOB o USD');

        new ServiceCost(
            monto: $this->monto,
            moneda: 'EUR',
            vigencia: $this->vigencia
        );
    }

    public function testEqualsReturnsTrueForSameValues(): void
    {
        $otherCost = new ServiceCost(
            monto: $this->monto,
            moneda: $this->moneda,
            vigencia: new DateTimeImmutable('2024-02-01') // Diferente fecha pero mismo monto y moneda
        );

        $this->assertTrue($this->serviceCost->equals($otherCost));
    }

    public function testEqualsReturnsFalseForDifferentValues(): void
    {
        $otherCost = new ServiceCost(
            monto: 200.00,
            moneda: 'USD',
            vigencia: $this->vigencia
        );

        $this->assertFalse($this->serviceCost->equals($otherCost));
    }

    public function testServiceCostEquality(): void
    {
        $cost1 = new ServiceCost(100.00, 'BOB', $this->vigencia);
        $cost2 = new ServiceCost(100.00, 'BOB', new DateTimeImmutable('2025-12-31'));
        $cost3 = new ServiceCost(200.00, 'BOB', $this->vigencia);
        $cost4 = new ServiceCost(100.00, 'USD', $this->vigencia);

        $this->assertTrue($cost1->equals($cost2)); // Mismos montos y moneda, diferente vigencia
        $this->assertFalse($cost1->equals($cost3)); // Diferente monto
        $this->assertFalse($cost1->equals($cost4)); // Diferente moneda
    }

    #[DataProvider('validCostProvider')]
    public function testAcceptsValidCosts(float $monto, string $moneda): void
    {
        $cost = new ServiceCost($monto, $moneda, $this->vigencia);
        $this->assertEquals($monto, $cost->getMonto());
        $this->assertEquals($moneda, $cost->getMoneda());
    }

    public static function validCostProvider(): array
    {
        return [
            'minimal cost BOB' => [0.01, 'BOB'],
            'typical cost BOB' => [100.00, 'BOB'],
            'large cost BOB' => [9999.99, 'BOB'],
            'minimal cost USD' => [0.01, 'USD'],
            'typical cost USD' => [100.00, 'USD'],
            'large cost USD' => [9999.99, 'USD'],
        ];
    }

    public function testVigenciaIsStored(): void
    {
        $vigencia = new DateTimeImmutable('2024-06-30');
        $cost = new ServiceCost(100.00, 'BOB', $vigencia);
        $this->assertEquals($vigencia, $cost->getVigencia());
    }
} 