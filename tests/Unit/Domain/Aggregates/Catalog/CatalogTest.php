<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregates\Catalog;

use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\Events\ServiceAdded;
use Commercial\Domain\Exceptions\CatalogException;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class CatalogTest extends TestCase
{
    private Catalog $catalog;
    private Service $service;
    private string $defaultCatalogId;
    private string $defaultServiceId;

    protected function setUp(): void
    {
        $this->defaultCatalogId = 'catalog-123';
        $this->defaultServiceId = 'service-456';

        $this->catalog = Catalog::create(
            $this->defaultCatalogId,
            'Test Catalog',
            ServiceStatus::ACTIVO
        );

        $this->service = new Service(
            $this->defaultServiceId,
            'Test Service',
            'Test Description',
            new ServiceCost(100.00, 'BOB', new DateTimeImmutable('2024-12-31')),
            'tipo-789',
            ServiceStatus::ACTIVO,
            $this->defaultCatalogId
        );
    }

    public function testCreateCatalog(): void
    {
        $this->assertEquals($this->defaultCatalogId, $this->catalog->getId());
        $this->assertEquals('Test Catalog', $this->catalog->getNombre());
        $this->assertEquals(ServiceStatus::ACTIVO, $this->catalog->getEstado());
    }

    public function testAddService(): void
    {
        $this->catalog->addService($this->service);
        $services = $this->catalog->getServices();
        
        $this->assertCount(1, $services);
        $this->assertEquals($this->service, $services[0]);
    }

    public function testCannotAddDuplicateService(): void
    {
        $this->catalog->addService($this->service);
        
        $this->expectException(CatalogException::class);
        $this->catalog->addService($this->service);
    }

    public function testRemoveService(): void
    {
        $this->catalog->addService($this->service);
        $this->catalog->removeService($this->defaultServiceId);
        
        $services = $this->catalog->getServices();
        $this->assertCount(0, $services);
        $this->assertNull($this->catalog->getService($this->defaultServiceId));
    }

    public function testCannotRemoveNonExistentService(): void
    {
        $this->expectException(CatalogException::class);
        $this->catalog->removeService('non-existent-id');
    }

    public function testUpdateService(): void
    {
        $this->catalog->addService($this->service);
        
        $updatedService = new Service(
            $this->defaultServiceId,
            'Updated Service',
            'Updated Description',
            new ServiceCost(200.00, 'BOB', new DateTimeImmutable('2024-12-31')),
            'tipo-789',
            ServiceStatus::ACTIVO,
            $this->defaultCatalogId
        );
        
        $this->catalog->updateService($updatedService);
        
        $retrievedService = $this->catalog->getService($this->defaultServiceId);
        $this->assertEquals('Updated Service', $retrievedService->getNombre());
        $this->assertEquals('Updated Description', $retrievedService->getDescripcion());
    }

    public function testCannotUpdateNonExistentService(): void
    {
        $this->expectException(CatalogException::class);
        $this->catalog->updateService($this->service);
    }

    public function testUpdateEstado(): void
    {
        $this->catalog->updateEstado(ServiceStatus::INACTIVO);
        $this->assertEquals(ServiceStatus::INACTIVO, $this->catalog->getEstado());
    }

    public function testUpdateNombre(): void
    {
        $this->catalog->updateNombre('New Catalog Name');
        $this->assertEquals('New Catalog Name', $this->catalog->getNombre());
    }

    public function testStatusChecks(): void
    {
        // Test active status
        $this->assertTrue($this->catalog->isActive());
        $this->assertFalse($this->catalog->isInactive());
        $this->assertFalse($this->catalog->isSuspended());
        
        // Test inactive status
        $this->catalog->updateEstado(ServiceStatus::INACTIVO);
        $this->assertFalse($this->catalog->isActive());
        $this->assertTrue($this->catalog->isInactive());
        $this->assertFalse($this->catalog->isSuspended());
        
        // Test suspended status
        $this->catalog->updateEstado(ServiceStatus::SUSPENDIDO);
        $this->assertFalse($this->catalog->isActive());
        $this->assertFalse($this->catalog->isInactive());
        $this->assertTrue($this->catalog->isSuspended());
    }

    public function testCombinedStatusChecks(): void
    {
        // Test active status combinations
        $this->assertTrue($this->catalog->isActiveOrInactive());
        $this->assertTrue($this->catalog->isActiveOrSuspended());
        $this->assertFalse($this->catalog->isInactiveOrSuspended());
        
        // Test inactive status combinations
        $this->catalog->updateEstado(ServiceStatus::INACTIVO);
        $this->assertTrue($this->catalog->isActiveOrInactive());
        $this->assertFalse($this->catalog->isActiveOrSuspended());
        $this->assertTrue($this->catalog->isInactiveOrSuspended());
        
        // Test suspended status combinations
        $this->catalog->updateEstado(ServiceStatus::SUSPENDIDO);
        $this->assertFalse($this->catalog->isActiveOrInactive());
        $this->assertTrue($this->catalog->isActiveOrSuspended());
        $this->assertTrue($this->catalog->isInactiveOrSuspended());
    }

    public function testEventHandling(): void
    {
        $this->catalog->addService($this->service);
        
        $events = $this->catalog->getEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ServiceAdded::class, $events[0]);
        
        // Verificar el evento sin acceder a propiedades privadas
        $event = $events[0];
        $this->assertInstanceOf(ServiceAdded::class, $event);
        
        $this->catalog->clearEvents();
        $this->assertCount(0, $this->catalog->getEvents());
    }
} 