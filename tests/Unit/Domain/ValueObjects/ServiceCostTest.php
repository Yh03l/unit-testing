<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Commercial\Domain\ValueObjects\ServiceCost;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use DateTimeImmutable;

class ServiceCostTest extends TestCase
{
    private DateTimeImmutable $defaultVigencia;

    protected function setUp(): void
    {
        $this->defaultVigencia = new DateTimeImmutable('2024-12-31');
    }

    public function testCreateValidServiceCost(): void
    {
        $cost = new ServiceCost(100.00, 'BOB', $this->defaultVigencia);
        $this->assertEquals(100.00, $cost->getMonto());
        $this->assertEquals('BOB', $cost->getMoneda());
    }

    public function testThrowsExceptionWhenMontoIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ServiceCost(-100.00, 'BOB', $this->defaultVigencia);
    }

    public function testThrowsExceptionWhenMonedaIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ServiceCost(100.00, 'EUR', $this->defaultVigencia);
    }

    public function testServiceCostEquality(): void
    {
        $cost1 = new ServiceCost(100.00, 'BOB', $this->defaultVigencia);
        $cost2 = new ServiceCost(100.00, 'BOB', $this->defaultVigencia);
        $cost3 = new ServiceCost(200.00, 'BOB', $this->defaultVigencia);

        $this->assertTrue($cost1->equals($cost2));
        $this->assertFalse($cost1->equals($cost3));
    }

    #[DataProvider('validCostProvider')]
    public function testAcceptsValidCosts(float $monto, string $moneda): void
    {
        $cost = new ServiceCost($monto, $moneda, $this->defaultVigencia);
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