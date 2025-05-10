<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\AddService;

use Commercial\Application\Commands\AddService\AddServiceCommand;
use Commercial\Application\Commands\AddService\AddServiceHandler;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceCost;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AddServiceHandlerTest extends MockeryTestCase
{
	private AddServiceHandler $handler;
	private CatalogRepository $repository;
	private string $catalogId;
	private string $nombre;
	private string $descripcion;
	private float $costo;
	private string $moneda;
	private DateTimeImmutable $vigencia;
	private string $tipoServicioId;
	private Catalog $catalog;

	protected function setUp(): void
	{
		$this->repository = Mockery::mock(CatalogRepository::class);
		$this->handler = new AddServiceHandler($this->repository);

		$this->catalogId = 'catalog-123';
		$this->nombre = 'Test Service';
		$this->descripcion = 'Test Description';
		$this->costo = 100.0;
		$this->moneda = 'BOB';
		$this->vigencia = new DateTimeImmutable('2024-12-31');
		$this->tipoServicioId = 'tipo-123';

		$this->catalog = Mockery::mock(Catalog::class);
	}

	public function testHandleAddsServiceSuccessfully(): void
	{
		// Arrange
		$command = new AddServiceCommand(
			$this->catalogId,
			$this->nombre,
			$this->descripcion,
			$this->costo,
			$this->moneda,
			$this->vigencia,
			$this->tipoServicioId
		);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($this->catalog);

		$this->catalog
			->shouldReceive('addService')
			->once()
			->with(Mockery::type(Service::class));

		$this->repository->shouldReceive('save')->with($this->catalog)->once();

		// Act
		$this->handler->__invoke($command);

		// Assert
		$this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
	}

	public function testHandleThrowsExceptionWhenCatalogNotFound(): void
	{
		// Arrange
		$command = new AddServiceCommand(
			$this->catalogId,
			$this->nombre,
			$this->descripcion,
			$this->costo,
			$this->moneda,
			$this->vigencia,
			$this->tipoServicioId
		);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn(null);

		// Assert
		$this->expectException(CatalogException::class);
		$this->expectExceptionMessage("Catálogo con ID {$this->catalogId} no encontrado");

		// Act
		$this->handler->__invoke($command);
	}

	public function testHandleCreatesServiceWithCorrectData(): void
	{
		// Arrange
		$command = new AddServiceCommand(
			$this->catalogId,
			$this->nombre,
			$this->descripcion,
			$this->costo,
			$this->moneda,
			$this->vigencia,
			$this->tipoServicioId
		);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($this->catalog);

		$this->catalog
			->shouldReceive('addService')
			->once()
			->with(
				Mockery::on(function (Service $service) {
					return $service->getNombre() === $this->nombre &&
						$service->getDescripcion() === $this->descripcion &&
						$service->getCosto()->getMonto() === $this->costo &&
						$service->getCosto()->getMoneda() === $this->moneda &&
						$service->getCosto()->getVigencia() === $this->vigencia &&
						$service->getTipoServicioId() === $this->tipoServicioId;
				})
			);

		$this->repository->shouldReceive('save')->with($this->catalog)->once();

		// Act
		$this->handler->__invoke($command);

		// Assert
		$this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
	}

	public function testInvokeCallsHandle(): void
	{
		// Arrange
		$command = new AddServiceCommand(
			$this->catalogId,
			$this->nombre,
			$this->descripcion,
			$this->costo,
			$this->moneda,
			$this->vigencia,
			$this->tipoServicioId
		);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($this->catalog);

		$this->catalog
			->shouldReceive('addService')
			->once()
			->with(Mockery::type(Service::class));

		$this->repository->shouldReceive('save')->with($this->catalog)->once();

		// Act
		$this->handler->__invoke($command);

		// Assert
		$this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
	}

	public function testHandleCallsInvoke(): void
	{
		// Arrange
		$command = new AddServiceCommand(
			$this->catalogId,
			$this->nombre,
			$this->descripcion,
			$this->costo,
			$this->moneda,
			$this->vigencia,
			$this->tipoServicioId
		);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($this->catalog);

		$this->catalog
			->shouldReceive('addService')
			->once()
			->with(Mockery::type(Service::class));

		$this->repository->shouldReceive('save')->with($this->catalog)->once();

		// Act
		$this->handler->handle($command);

		// Assert
		$this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
	}
}
