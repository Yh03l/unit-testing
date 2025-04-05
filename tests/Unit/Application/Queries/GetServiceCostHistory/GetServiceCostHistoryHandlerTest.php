<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetServiceCostHistory;

use Commercial\Application\Queries\GetServiceCostHistory\GetServiceCostHistoryHandler;
use Commercial\Application\Queries\GetServiceCostHistory\GetServiceCostHistoryQuery;
use Commercial\Application\DTOs\ServiceCostHistoryDTO;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\Exceptions\CatalogException;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @group skip-ci
 */
class GetServiceCostHistoryHandlerTest extends MockeryTestCase
{
	private GetServiceCostHistoryHandler $handler;
	private ServiceRepository|MockInterface $repository;
	private string $serviceId;
	private Service|MockInterface $service;

	protected function setUp(): void
	{
		parent::setUp();
		$this->repository = Mockery::mock(ServiceRepository::class);
		$this->handler = new GetServiceCostHistoryHandler($this->repository);
		$this->serviceId = 'service-123';
		$this->service = Mockery::mock(Service::class);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}

	public function testHandleReturnsServiceCostHistoryWhenExists(): void
	{
		// Arrange
		$query = new GetServiceCostHistoryQuery($this->serviceId);
		$cost1 = new ServiceCost(100.0, 'BOB', new DateTimeImmutable('2024-01-01'));
		$cost2 = new ServiceCost(150.0, 'BOB', new DateTimeImmutable('2024-02-01'));
		$costHistory = [$cost1, $cost2];

		$this->repository
			->shouldReceive('findById')
			->with($this->serviceId)
			->once()
			->andReturn($this->service);

		$this->repository
			->shouldReceive('getServiceCostHistory')
			->with($this->serviceId)
			->once()
			->andReturn($costHistory);

		// Act
		$result = $this->handler->handle($query);

		// Assert
		$this->assertCount(2, $result);
		$this->assertContainsOnlyInstancesOf(ServiceCostHistoryDTO::class, $result);
		$this->assertEquals(100.0, $result[0]->getMonto());
		$this->assertEquals('BOB', $result[0]->getMoneda());
		$this->assertEquals(150.0, $result[1]->getMonto());
		$this->assertEquals('BOB', $result[1]->getMoneda());
	}

	public function testHandleThrowsExceptionWhenServiceNotFound(): void
	{
		// Arrange
		$query = new GetServiceCostHistoryQuery($this->serviceId);

		$this->repository
			->shouldReceive('findById')
			->with($this->serviceId)
			->once()
			->andReturn(null);

		// Assert
		$this->expectException(CatalogException::class);
		$this->expectExceptionMessage("No se encontró el servicio con ID {$this->serviceId}");

		// Act
		$this->handler->handle($query);
	}

	public function testHandleThrowsExceptionWhenNoHistoryExists(): void
	{
		// Arrange
		$query = new GetServiceCostHistoryQuery($this->serviceId);

		$this->repository
			->shouldReceive('findById')
			->with($this->serviceId)
			->once()
			->andReturn($this->service);

		$this->repository
			->shouldReceive('getServiceCostHistory')
			->with($this->serviceId)
			->once()
			->andReturn([]);

		// Assert
		$this->expectException(CatalogException::class);
		$this->expectExceptionMessage(
			"No se encontró historial de costos para el servicio con ID {$this->serviceId}"
		);

		// Act
		$this->handler->handle($query);
	}

	public function testInvokeCallsHandle(): void
	{
		// Arrange
		$query = new GetServiceCostHistoryQuery($this->serviceId);
		$cost = new ServiceCost(100.0, 'BOB', new DateTimeImmutable('2024-01-01'));
		$costHistory = [$cost];

		$this->repository
			->shouldReceive('findById')
			->with($this->serviceId)
			->once()
			->andReturn($this->service);

		$this->repository
			->shouldReceive('getServiceCostHistory')
			->with($this->serviceId)
			->once()
			->andReturn($costHistory);

		// Act
		$result = ($this->handler)($query);

		// Assert
		$this->assertCount(1, $result);
		$this->assertContainsOnlyInstancesOf(ServiceCostHistoryDTO::class, $result);
		$this->assertEquals(100.0, $result[0]->getMonto());
		$this->assertEquals('BOB', $result[0]->getMoneda());
	}
}
