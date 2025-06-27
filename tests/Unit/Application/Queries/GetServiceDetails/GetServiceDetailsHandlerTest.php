<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetServiceDetails;

use Commercial\Application\Queries\GetServiceDetails\GetServiceDetailsQuery;
use Commercial\Application\Queries\GetServiceDetails\GetServiceDetailsHandler;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Domain\Enums\TipoServicio;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Application\DTOs\ServiceDTO;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class GetServiceDetailsHandlerTest extends MockeryTestCase
{
	private GetServiceDetailsHandler $handler;
	private ServiceRepository $repository;
	private string $serviceId;
	private GetServiceDetailsQuery $query;
	private DateTimeImmutable $vigencia;

	protected function setUp(): void
	{
		$this->serviceId = 'service-123';
		$this->vigencia = new DateTimeImmutable('2024-12-31');
		$this->repository = Mockery::mock(ServiceRepository::class);
		$this->handler = new GetServiceDetailsHandler($this->repository);
		$this->query = new GetServiceDetailsQuery($this->serviceId);
	}

	public function testHandleReturnsServiceDTOWhenServiceExists(): void
	{
		$service = Mockery::mock(Service::class);
		$service->shouldReceive('getId')->andReturn($this->serviceId);
		$service->shouldReceive('getNombre')->andReturn('Test Service');
		$service->shouldReceive('getDescripcion')->andReturn('Test Description');
		$service
			->shouldReceive('getCosto')
			->andReturn(new ServiceCost(100.0, 'BOB', $this->vigencia));
		$service
			->shouldReceive('getTipoServicio')
			->andReturn(TipoServicio::fromString('asesoramiento'));
		$service->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);
		$service->shouldReceive('getCatalogoId')->andReturn('catalog-123');

		$this->repository
			->shouldReceive('findById')
			->once()
			->with($this->serviceId)
			->andReturn($service);

		$result = $this->handler->handle($this->query);

		$this->assertInstanceOf(ServiceDTO::class, $result);
		$this->assertEquals($this->serviceId, $result->id);
		$this->assertEquals('Test Service', $result->nombre);
		$this->assertEquals('Test Description', $result->descripcion);
		$this->assertEquals(100.0, $result->monto);
		$this->assertEquals('BOB', $result->moneda);
		$this->assertEquals($this->vigencia, $result->vigencia);
		$this->assertEquals('asesoramiento', $result->tipo_servicio_id);
		$this->assertEquals('activo', $result->estado);
		$this->assertEquals('catalog-123', $result->catalogo_id);
	}

	public function testHandleThrowsExceptionWhenServiceNotFound(): void
	{
		$this->repository
			->shouldReceive('findById')
			->once()
			->with($this->serviceId)
			->andReturn(null);

		$this->expectException(CatalogException::class);
		$this->expectExceptionMessage("No se encontró el servicio con ID {$this->serviceId}");

		$this->handler->handle($this->query);
	}

	public function testInvokeCallsHandle(): void
	{
		$service = Mockery::mock(Service::class);
		$service->shouldReceive('getId')->andReturn($this->serviceId);
		$service->shouldReceive('getNombre')->andReturn('Test Service');
		$service->shouldReceive('getDescripcion')->andReturn('Test Description');
		$service
			->shouldReceive('getCosto')
			->andReturn(new ServiceCost(100.0, 'BOB', $this->vigencia));
		$service
			->shouldReceive('getTipoServicio')
			->andReturn(TipoServicio::fromString('asesoramiento'));
		$service->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);
		$service->shouldReceive('getCatalogoId')->andReturn('catalog-123');

		$this->repository
			->shouldReceive('findById')
			->once()
			->with($this->serviceId)
			->andReturn($service);

		$result = $this->handler->__invoke($this->query);

		$this->assertInstanceOf(ServiceDTO::class, $result);
		$this->assertEquals($this->serviceId, $result->id);
	}
}
