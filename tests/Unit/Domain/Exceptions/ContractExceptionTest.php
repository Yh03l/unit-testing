<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use Commercial\Domain\Exceptions\ContractException;
use PHPUnit\Framework\TestCase;

class ContractExceptionTest extends TestCase
{
    public function testNotFound(): void
    {
        $id = 'test-id';
        $exception = ContractException::notFound($id);
        
        $this->assertInstanceOf(ContractException::class, $exception);
        $this->assertEquals("Contrato con ID {$id} no encontrado", $exception->getMessage());
    }

    public function testInvalidState(): void
    {
        $currentState = 'ACTIVO';
        $expectedState = 'PENDIENTE';
        $exception = ContractException::invalidState($currentState, $expectedState);
        
        $this->assertInstanceOf(ContractException::class, $exception);
        $this->assertEquals(
            "Estado inválido del contrato. Estado actual: {$currentState}, Estado esperado: {$expectedState}",
            $exception->getMessage()
        );
    }

    public function testInvalidDates(): void
    {
        $exception = ContractException::invalidDates();
        
        $this->assertInstanceOf(ContractException::class, $exception);
        $this->assertEquals("Las fechas del contrato son inválidas", $exception->getMessage());
    }
} 