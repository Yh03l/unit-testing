<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ServiceStatusTest extends TestCase
{
    public function testCreateFromValidString(): void
    {
        $status = ServiceStatus::fromString('activo');
        $this->assertSame(ServiceStatus::ACTIVO, $status);
    }

    public function testThrowsExceptionForInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ServiceStatus::fromString('invalid-status');
    }

    public function testStatusToString(): void
    {
        $this->assertEquals('activo', ServiceStatus::ACTIVO->toString());
        $this->assertEquals('inactivo', ServiceStatus::INACTIVO->toString());
        $this->assertEquals('suspendido', ServiceStatus::SUSPENDIDO->toString());
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