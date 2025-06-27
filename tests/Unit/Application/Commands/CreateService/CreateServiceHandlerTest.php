<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CreateService;

use Commercial\Application\Commands\CreateService\CreateServiceCommand;
use Commercial\Application\Commands\CreateService\CreateServiceHandler;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CreateServiceHandlerTest extends MockeryTestCase
{
	private CreateServiceHandler $handler;
	private ServiceRepository $serviceRepository;
	private CatalogRepository $catalogRepository;
	private CreateServiceCommand $command;
	private string $catalogId;

	protected function setUp(): void
	{
		$this->serviceRepository = Mockery::mock(ServiceRepository::class);
		$this->catalogRepository = Mockery::mock(CatalogRepository::class);
		$this->handler = new CreateServiceHandler(
			$this->serviceRepository,
			$this->catalogRepository
		);

		$this->catalogId = 'catalog-123';
		$this->command = new CreateServiceCommand(
			'Test Service',
			'Test Description',
			100.0,
			'BOB',
			new DateTimeImmutable('2024-12-31'),
			'asesoramiento',
			$this->catalogId
		);
	}

	public function testHandleCreatesServiceWhenCatalogIsActive(): void
	{
		$catalog = Mockery::mock(Catalog::class);
		$catalog->shouldReceive('isActive')->once()->andReturn(true);

		$this->catalogRepository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($catalog);

		$this->serviceRepository
			->shouldReceive('save')
			->once()
			->with(Mockery::type(Service::class))
			->andReturnNull();

		$this->handler->handle($this->command);
		$this->assertTrue(true); // Verifica que no se lanzaron excepciones
	}

	public function testHandleThrowsExceptionWhenCatalogNotFound(): void
	{
		$this->catalogRepository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn(null);

		$this->expectException(CatalogException::class);
		$this->expectExceptionMessage("El catálogo con ID {$this->catalogId} no está activo");

		$this->handler->handle($this->command);
	}

	public function testHandleThrowsExceptionWhenCatalogNotActive(): void
	{
		$catalog = Mockery::mock(Catalog::class);
		$catalog->shouldReceive('isActive')->once()->andReturn(false);

		$this->catalogRepository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($catalog);

		$this->expectException(CatalogException::class);
		$this->expectExceptionMessage("El catálogo con ID {$this->catalogId} no está activo");

		$this->handler->handle($this->command);
	}

	public function testCreatedServiceHasCorrectData(): void
	{
		$catalog = Mockery::mock(Catalog::class);
		$catalog->shouldReceive('isActive')->once()->andReturn(true);

		$this->catalogRepository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($catalog);

		$savedService = null;

		$this->serviceRepository
			->shouldReceive('save')
			->once()
			->with(
				Mockery::on(function (Service $service) use (&$savedService) {
					$savedService = $service;
					return true;
				})
			);

		$this->handler->handle($this->command);

		$this->assertNotNull($savedService);
		$this->assertEquals('Test Service', $savedService->getNombre());
		$this->assertEquals('Test Description', $savedService->getDescripcion());
		$this->assertEquals(100.0, $savedService->getCosto()->getMonto());
		$this->assertEquals('BOB', $savedService->getCosto()->getMoneda());
		$this->assertEquals('asesoramiento', $savedService->getTipoServicio()->toString());
		$this->assertEquals(ServiceStatus::ACTIVO, $savedService->getEstado());
		$this->assertEquals($this->catalogId, $savedService->getCatalogoId());
	}
}
