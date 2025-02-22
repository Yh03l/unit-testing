<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ServiceStatusTest extends TestCase
{
    public function testFromStringReturnsCorrectEnum(): void
    {
        $this->assertEquals(ServiceStatus::ACTIVO, ServiceStatus::fromString('activo'));
        $this->assertEquals(ServiceStatus::INACTIVO, ServiceStatus::fromString('inactivo'));
        $this->assertEquals(ServiceStatus::SUSPENDIDO, ServiceStatus::fromString('suspendido'));

        // Probar con mayúsculas
        $this->assertEquals(ServiceStatus::ACTIVO, ServiceStatus::fromString('ACTIVO'));
        $this->assertEquals(ServiceStatus::INACTIVO, ServiceStatus::fromString('INACTIVO'));
        $this->assertEquals(ServiceStatus::SUSPENDIDO, ServiceStatus::fromString('SUSPENDIDO'));
    }

    public function testFromStringThrowsExceptionForInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Estado de catálogo inválido');
        ServiceStatus::fromString('invalid');
    }

    public function testToStringReturnsCorrectString(): void
    {
        $this->assertEquals('activo', ServiceStatus::ACTIVO->toString());
        $this->assertEquals('inactivo', ServiceStatus::INACTIVO->toString());
        $this->assertEquals('suspendido', ServiceStatus::SUSPENDIDO->toString());
    }

    public function testEnumValues(): void
    {
        $this->assertEquals('activo', ServiceStatus::ACTIVO->value);
        $this->assertEquals('inactivo', ServiceStatus::INACTIVO->value);
        $this->assertEquals('suspendido', ServiceStatus::SUSPENDIDO->value);
    }

    public function testEnumCases(): void
    {
        $cases = ServiceStatus::cases();
        $this->assertCount(3, $cases);
        $this->assertContains(ServiceStatus::ACTIVO, $cases);
        $this->assertContains(ServiceStatus::INACTIVO, $cases);
        $this->assertContains(ServiceStatus::SUSPENDIDO, $cases);
    }

    public function testEnumComparison(): void
    {
        $status1 = ServiceStatus::fromString('activo');
        $status2 = ServiceStatus::ACTIVO;
        $status3 = ServiceStatus::fromString('ACTIVO');

        $this->assertTrue($status1 === $status2);
        $this->assertTrue($status2 === $status3);
        $this->assertTrue($status1 === $status3);
    }

    #[DataProvider('validStatusProvider')]
    public function testAcceptsValidStatuses(string $input, ServiceStatus $expected): void
    {
        $status = ServiceStatus::fromString($input);
        $this->assertSame($expected, $status);
    }

    public static function validStatusProvider(): array
    {
        return [
            'activo lowercase' => ['activo', ServiceStatus::ACTIVO],
            'inactivo lowercase' => ['inactivo', ServiceStatus::INACTIVO],
            'suspendido lowercase' => ['suspendido', ServiceStatus::SUSPENDIDO],
            'ACTIVO uppercase' => ['ACTIVO', ServiceStatus::ACTIVO],
            'INACTIVO uppercase' => ['INACTIVO', ServiceStatus::INACTIVO],
            'SUSPENDIDO uppercase' => ['SUSPENDIDO', ServiceStatus::SUSPENDIDO],
            'Activo mixed case' => ['Activo', ServiceStatus::ACTIVO],
            'Inactivo mixed case' => ['Inactivo', ServiceStatus::INACTIVO],
            'Suspendido mixed case' => ['Suspendido', ServiceStatus::SUSPENDIDO],
        ];
    }

    #[DataProvider('invalidStatusProvider')]
    public function testThrowsExceptionForInvalidStatuses(string $invalidStatus): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ServiceStatus::fromString($invalidStatus);
    }

    public static function invalidStatusProvider(): array
    {
        return [
            'empty string' => [''],
            'invalid value' => ['pending'],
            'with spaces' => ['activo '],
            'partial match' => ['act'],
            'special characters' => ['activo!'],
        ];
    }
}