<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Queries\GetCatalog;

use Commercial\Application\Queries\GetCatalog\GetCatalogHandler;
use Commercial\Application\Queries\GetCatalog\GetCatalogQuery;
use Commercial\Application\DTOs\CatalogDTO;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
#[\PHPUnit\Framework\Attributes\Group('skip-ci')]
class GetCatalogHandlerTest extends MockeryTestCase
{
	private GetCatalogHandler $handler;
	private CatalogRepository|MockInterface $repository;
	private string $catalogId;
	private Catalog|MockInterface $catalog;

	protected function setUp(): void
	{
		parent::setUp();
		$this->repository = Mockery::mock(CatalogRepository::class);
		$this->handler = new GetCatalogHandler($this->repository);
		$this->catalogId = 'catalog-123';
		$this->catalog = Mockery::mock(Catalog::class);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}

	public function testHandleReturnsCatalogDTOWhenExists(): void
	{
		// Arrange
		$query = new GetCatalogQuery($this->catalogId);

		$this->catalog->shouldReceive('getId')->andReturn($this->catalogId);
		$this->catalog->shouldReceive('getNombre')->andReturn('Test Catalog');
		$this->catalog->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);
		$this->catalog->shouldReceive('getServices')->andReturn([]);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($this->catalog);

		// Act
		$result = $this->handler->handle($query);

		// Assert
		$this->assertInstanceOf(CatalogDTO::class, $result);
		$this->assertEquals($this->catalogId, $result->id);
		$this->assertEquals('Test Catalog', $result->nombre);
		$this->assertEquals(ServiceStatus::ACTIVO, $result->estado);
		$this->assertEquals([], $result->services);
	}

	public function testHandleThrowsExceptionWhenCatalogNotFound(): void
	{
		// Arrange
		$query = new GetCatalogQuery($this->catalogId);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn(null);

		// Assert
		$this->expectException(CatalogException::class);
		$this->expectExceptionMessage("Catálogo con ID {$this->catalogId} no encontrado");

		// Act
		$this->handler->handle($query);
	}

	public function testInvokeCallsHandle(): void
	{
		// Arrange
		$query = new GetCatalogQuery($this->catalogId);

		$this->catalog->shouldReceive('getId')->andReturn($this->catalogId);
		$this->catalog->shouldReceive('getNombre')->andReturn('Test Catalog');
		$this->catalog->shouldReceive('getEstado')->andReturn(ServiceStatus::ACTIVO);
		$this->catalog->shouldReceive('getServices')->andReturn([]);

		$this->repository
			->shouldReceive('findById')
			->with($this->catalogId)
			->once()
			->andReturn($this->catalog);

		// Act
		$result = ($this->handler)($query);

		// Assert
		$this->assertInstanceOf(CatalogDTO::class, $result);
		$this->assertEquals($this->catalogId, $result->id);
		$this->assertEquals('Test Catalog', $result->nombre);
		$this->assertEquals(ServiceStatus::ACTIVO, $result->estado);
		$this->assertEquals([], $result->services);
	}
}
