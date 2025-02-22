<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\ListActiveServices;

use Commercial\Application\Queries\ListActiveServices\ListActiveServicesHandler;
use Commercial\Application\Queries\ListActiveServices\ListActiveServicesQuery;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Application\DTOs\ServiceDTO;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class ListActiveServicesHandlerTest extends MockeryTestCase
{
    private ListActiveServicesHandler $handler;
    private ServiceRepository|MockInterface $repository;
    private DateTimeImmutable $vigencia;
    private Service|MockInterface $service1;
    private Service|MockInterface $service2;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(ServiceRepository::class);
        $this->handler = new ListActiveServicesHandler($this->repository);
        
        $this->vigencia = new DateTimeImmutable('2024-12-31');
        
        // Crear mocks de servicios
        $this->service1 = Mockery::mock(Service::class);
        $this->service1->shouldReceive('getId')->andReturn('service-1');
        $this->service1->shouldReceive('getNombre')->andReturn('Servicio 1');
        $this->service1->shouldReceive('getDescripcion')->andReturn('Descripción 1');
        $this->service1->shouldReceive('getCosto')->andReturn(
            new ServiceCost(100.00, 'BOB', $this->vigencia)
        );
        $this->service1->shouldReceive('getTipoServicioId')->andReturn('tipo-1');
        $this->service1->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);
        $this->service1->shouldReceive('getCatalogoId')->andReturn('catalog-1');

        $this->service2 = Mockery::mock(Service::class);
        $this->service2->shouldReceive('getId')->andReturn('service-2');
        $this->service2->shouldReceive('getNombre')->andReturn('Servicio 2');
        $this->service2->shouldReceive('getDescripcion')->andReturn('Descripción 2');
        $this->service2->shouldReceive('getCosto')->andReturn(
            new ServiceCost(200.00, 'BOB', $this->vigencia)
        );
        $this->service2->shouldReceive('getTipoServicioId')->andReturn('tipo-2');
        $this->service2->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);
        $this->service2->shouldReceive('getCatalogoId')->andReturn('catalog-2');
    }

    public function testHandleReturnsAllActiveServicesWhenNoCatalogId(): void
    {
        $query = new ListActiveServicesQuery();
        
        $this->repository->shouldReceive('findByStatus')
            ->once()
            ->with(ServiceStatus::ACTIVO, null)
            ->andReturn([$this->service1, $this->service2]);

        $result = $this->handler->handle($query);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ServiceDTO::class, $result);
        
        // Verificar el primer servicio
        $this->assertEquals('service-1', $result[0]->id);
        $this->assertEquals('Servicio 1', $result[0]->nombre);
        $this->assertEquals('Descripción 1', $result[0]->descripcion);
        $this->assertEquals(100.00, $result[0]->monto);
        $this->assertEquals('BOB', $result[0]->moneda);
        $this->assertEquals($this->vigencia, $result[0]->vigencia);
        $this->assertEquals('tipo-1', $result[0]->tipo_servicio_id);
        $this->assertEquals('activo', $result[0]->estado);
        $this->assertEquals('catalog-1', $result[0]->catalogo_id);

        // Verificar el segundo servicio
        $this->assertEquals('service-2', $result[1]->id);
        $this->assertEquals('Servicio 2', $result[1]->nombre);
    }

    public function testHandleReturnsFilteredActiveServicesByCatalogId(): void
    {
        $catalogId = 'catalog-1';
        $query = new ListActiveServicesQuery($catalogId);
        
        $this->repository->shouldReceive('findByStatus')
            ->once()
            ->with(ServiceStatus::ACTIVO, $catalogId)
            ->andReturn([$this->service1]);

        $result = $this->handler->handle($query);

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(ServiceDTO::class, $result);
        $this->assertEquals('service-1', $result[0]->id);
        $this->assertEquals('catalog-1', $result[0]->catalogo_id);
    }

    public function testHandleReturnsEmptyArrayWhenNoActiveServices(): void
    {
        $query = new ListActiveServicesQuery();
        
        $this->repository->shouldReceive('findByStatus')
            ->once()
            ->with(ServiceStatus::ACTIVO, null)
            ->andReturn([]);

        $result = $this->handler->handle($query);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testInvokeCallsHandle(): void
    {
        $query = new ListActiveServicesQuery();
        
        $this->repository->shouldReceive('findByStatus')
            ->once()
            ->with(ServiceStatus::ACTIVO, null)
            ->andReturn([$this->service1, $this->service2]);

        $result = $this->handler->__invoke($query);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ServiceDTO::class, $result);
    }
} 