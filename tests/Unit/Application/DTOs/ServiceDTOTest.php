<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTOs;

use Commercial\Application\DTOs\ServiceDTO;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\ValueObjects\ServiceStatus;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class ServiceDTOTest extends MockeryTestCase
{
    private string $id;
    private string $nombre;
    private string $descripcion;
    private float $monto;
    private string $moneda;
    private string $tipoServicioId;
    private string $catalogoId;
    private DateTimeImmutable $vigencia;
    private ServiceCost $costo;
    private Service|MockInterface $service;

    protected function setUp(): void
    {
        $this->id = 'service-123';
        $this->nombre = 'Test Service';
        $this->descripcion = 'Test Description';
        $this->monto = 100.00;
        $this->moneda = 'BOB';
        $this->tipoServicioId = 'tipo-123';
        $this->catalogoId = 'catalog-123';
        $this->vigencia = new DateTimeImmutable('2024-12-31 10:00:00');
        $this->costo = new ServiceCost($this->monto, $this->moneda, $this->vigencia);

        $this->service = Mockery::mock(Service::class);
        $this->service->shouldReceive('getId')->andReturn($this->id);
        $this->service->shouldReceive('getNombre')->andReturn($this->nombre);
        $this->service->shouldReceive('getDescripcion')->andReturn($this->descripcion);
        $this->service->shouldReceive('getTipoServicioId')->andReturn($this->tipoServicioId);
        $this->service->shouldReceive('getCatalogoId')->andReturn($this->catalogoId);
    }

    public function testFromEntity(): void
    {
        $this->service->shouldReceive('getCosto')->andReturn($this->costo);
        $this->service->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);

        $dto = ServiceDTO::fromEntity($this->service);

        $this->assertEquals($this->id, $dto->id);
        $this->assertEquals($this->nombre, $dto->nombre);
        $this->assertEquals($this->descripcion, $dto->descripcion);
        $this->assertEquals($this->monto, $dto->monto);
        $this->assertEquals($this->moneda, $dto->moneda);
        $this->assertEquals($this->tipoServicioId, $dto->tipo_servicio_id);
        $this->assertEquals('activo', $dto->estado);
        $this->assertEquals($this->catalogoId, $dto->catalogo_id);
        $this->assertEquals($this->vigencia, $dto->vigencia);
    }

    public function testToArray(): void
    {
        $this->service->shouldReceive('getCosto')->andReturn($this->costo);
        $this->service->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);

        $dto = ServiceDTO::fromEntity($this->service);
        $array = $dto->toArray();

        $expected = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'tipo_servicio_id' => $this->tipoServicioId,
            'estado' => 'activo',
            'catalogo_id' => $this->catalogoId,
            'vigencia' => $this->vigencia->format('Y-m-d H:i:s')
        ];

        $this->assertEquals($expected, $array);
    }

    public function testFromEntityWithDifferentServiceStatus(): void
    {
        $this->service->shouldReceive('getCosto')->andReturn($this->costo);
        $this->service->shouldReceive('getEstado')->andReturn(ServiceStatus::INACTIVO);
        
        $dto = ServiceDTO::fromEntity($this->service);
        
        $this->assertEquals('inactivo', $dto->estado);
    }

    public function testFromEntityWithDifferentCost(): void
    {
        $newCosto = new ServiceCost(200.00, 'USD', new DateTimeImmutable('2025-01-01 12:00:00'));
        $this->service->shouldReceive('getCosto')->andReturn($newCosto);
        $this->service->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);
        
        $dto = ServiceDTO::fromEntity($this->service);
        
        $this->assertEquals(200.00, $dto->monto);
        $this->assertEquals('USD', $dto->moneda);
        $this->assertEquals(new DateTimeImmutable('2025-01-01 12:00:00'), $dto->vigencia);
    }
} 