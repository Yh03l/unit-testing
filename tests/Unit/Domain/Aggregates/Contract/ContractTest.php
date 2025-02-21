<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregates\Contract;

use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\Events\ContractCreated;
use Commercial\Domain\Events\ContractActivated;
use Commercial\Domain\Events\ContractCancelled;
use Commercial\Domain\ValueObjects\ContractDate;
use PHPUnit\Framework\TestCase;

class ContractTest extends TestCase
{
    private string $id;
    private string $pacienteId;
    private string $servicioId;
    private ContractDate $contractDate;
    private Contract $contract;

    protected function setUp(): void
    {
        $this->id = 'test-id';
        $this->pacienteId = 'paciente-id';
        $this->servicioId = 'servicio-id';
        $this->contractDate = new ContractDate(
            new \DateTimeImmutable('tomorrow'),
            new \DateTimeImmutable('next week')
        );
        $this->contract = Contract::create(
            $this->id,
            $this->pacienteId,
            $this->servicioId,
            $this->contractDate
        );
    }

    public function testCreateContract(): void
    {
        $this->assertEquals($this->id, $this->contract->getId());
        $this->assertEquals($this->pacienteId, $this->contract->getPacienteId());
        $this->assertEquals($this->servicioId, $this->contract->getServicioId());
        $this->assertEquals($this->contractDate, $this->contract->getFechaContrato());
        $this->assertEquals('PENDIENTE', $this->contract->getEstado());

        $events = $this->contract->getEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ContractCreated::class, $events[0]);
    }

    public function testActivateContract(): void
    {
        $this->contract->activarContrato();

        $this->assertEquals('ACTIVO', $this->contract->getEstado());
        
        $events = $this->contract->getEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(ContractActivated::class, $events[1]);
    }

    public function testActivateContractThrowsExceptionWhenNotPending(): void
    {
        $this->contract->activarContrato();
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Solo se pueden activar contratos pendientes');
        
        $this->contract->activarContrato();
    }

    public function testCancelContract(): void
    {
        $this->contract->cancelarContrato();

        $this->assertEquals('CANCELADO', $this->contract->getEstado());
        
        $events = $this->contract->getEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(ContractCancelled::class, $events[1]);
    }

    public function testCancelContractThrowsExceptionWhenAlreadyCancelled(): void
    {
        $this->contract->cancelarContrato();
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El contrato ya está cancelado');
        
        $this->contract->cancelarContrato();
    }

    public function testGenerarFacturaThrowsExceptionWhenNotActive(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Solo se pueden generar facturas para contratos activos');
        
        $this->contract->generarFactura();
    }

    public function testGenerarFacturaWhenActive(): void
    {
        $this->contract->activarContrato();
        $this->contract->generarFactura();
        // No hay eventos para factura aún, pero el método no debería lanzar excepción
        $this->assertTrue(true);
    }

    public function testClearEvents(): void
    {
        $this->assertCount(1, $this->contract->getEvents());
        
        $this->contract->clearEvents();
        
        $this->assertCount(0, $this->contract->getEvents());
    }
} 