<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregates\Catalog;

use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class ServiceTest extends TestCase
{
    private Service $service;
    private ServiceCost $defaultCost;
    private string $defaultId;
    private string $defaultCatalogId;
    private string $defaultTipoServicioId;

    protected function setUp(): void
    {
        $this->defaultId = 'service-123';
        $this->defaultCatalogId = 'catalog-456';
        $this->defaultTipoServicioId = 'tipo-789';
        $this->defaultCost = new ServiceCost(
            100.00,
            'BOB',
            new DateTimeImmutable('2024-12-31')
        );

        $this->service = new Service(
            $this->defaultId,
            'Test Service',
            'Test Description',
            $this->defaultCost,
            $this->defaultTipoServicioId,
            ServiceStatus::ACTIVO,
            $this->defaultCatalogId
        );
    }

    public function testCreateValidService(): void
    {
        $this->assertEquals($this->defaultId, $this->service->getId());
        $this->assertEquals('Test Service', $this->service->getNombre());
        $this->assertEquals('Test Description', $this->service->getDescripcion());
        $this->assertEquals($this->defaultCost, $this->service->getCosto());
        $this->assertEquals($this->defaultTipoServicioId, $this->service->getTipoServicioId());
        $this->assertEquals(ServiceStatus::ACTIVO, $this->service->getEstado());
        $this->assertEquals($this->defaultCatalogId, $this->service->getCatalogoId());
    }

    public function testUpdateServiceInfo(): void
    {
        $this->service->update('New Name', 'New Description');
        
        $this->assertEquals('New Name', $this->service->getNombre());
        $this->assertEquals('New Description', $this->service->getDescripcion());
    }

    public function testCannotUpdateSuspendedService(): void
    {
        $this->service->updateEstado(ServiceStatus::SUSPENDIDO);
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No se puede modificar el servicio en su estado actual');
        
        $this->service->update('New Name', 'New Description');
    }

    public function testUpdateCost(): void
    {
        $newCost = new ServiceCost(200.00, 'BOB', new DateTimeImmutable('2024-12-31'));
        $this->service->updateCost($newCost);
        
        $this->assertEquals($newCost, $this->service->getCosto());
    }

    public function testCannotUpdateCostWhenInactive(): void
    {
        $this->service->updateEstado(ServiceStatus::INACTIVO);
        $newCost = new ServiceCost(200.00, 'BOB', new DateTimeImmutable('2024-12-31'));
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No se puede actualizar el costo del servicio en su estado actual');
        
        $this->service->updateCost($newCost);
    }

    public function testUpdateStatus(): void
    {
        $this->service->updateEstado(ServiceStatus::INACTIVO);
        $this->assertEquals(ServiceStatus::INACTIVO, $this->service->getEstado());
    }

    public function testCannotUpdateToActiveFromSuspended(): void
    {
        $this->service->updateEstado(ServiceStatus::SUSPENDIDO);
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No se puede actualizar el estado del servicio');
        
        $this->service->updateEstado(ServiceStatus::ACTIVO);
    }

    public function testIsActive(): void
    {
        $this->assertTrue($this->service->isActive());
        
        $this->service->updateEstado(ServiceStatus::INACTIVO);
        $this->assertFalse($this->service->isActive());
    }

    public function testCanUpdateStatus(): void
    {
        $this->assertTrue($this->service->canUpdateStatus(ServiceStatus::INACTIVO));
        $this->assertTrue($this->service->canUpdateStatus(ServiceStatus::SUSPENDIDO));
        
        $this->service->updateEstado(ServiceStatus::SUSPENDIDO);
        $this->assertFalse($this->service->canUpdateStatus(ServiceStatus::ACTIVO));
        $this->assertFalse($this->service->canUpdateStatus(ServiceStatus::INACTIVO));
    }

    public function testCanBeModified(): void
    {
        $this->assertTrue($this->service->canBeModified());
        
        $this->service->updateEstado(ServiceStatus::INACTIVO);
        $this->assertTrue($this->service->canBeModified());
        
        $this->service->updateEstado(ServiceStatus::SUSPENDIDO);
        $this->assertFalse($this->service->canBeModified());
    }

    public function testCanUpdateCost(): void
    {
        $this->assertTrue($this->service->canUpdateCost());
        
        $this->service->updateEstado(ServiceStatus::INACTIVO);
        $this->assertFalse($this->service->canUpdateCost());
        
        $this->service->updateEstado(ServiceStatus::SUSPENDIDO);
        $this->assertFalse($this->service->canUpdateCost());
    }

    public function testMagicGetMethod(): void
    {
        $this->assertEquals($this->defaultId, $this->service->id);
        $this->assertEquals('Test Service', $this->service->name);
        $this->assertEquals('Test Description', $this->service->description);
        $this->assertEquals(ServiceStatus::ACTIVO, $this->service->status);
        $this->assertEquals($this->defaultCost, $this->service->cost);
        $this->assertEquals($this->defaultCatalogId, $this->service->catalogId);
    }

    public function testMagicGetMethodThrowsExceptionForInvalidProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Propiedad invalid no existe');
        
        $this->service->invalid;
    }
} 