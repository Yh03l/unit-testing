<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Controllers;

use Commercial\Api\Controllers\CatalogController;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Application\Commands\CreateCatalog\CreateCatalogCommand;
use Commercial\Application\Commands\UpdateCatalog\UpdateCatalogCommand;
use Commercial\Application\Commands\DeleteCatalog\DeleteCatalogCommand;
use Commercial\Application\Queries\GetCatalog\GetCatalogQuery;
use Commercial\Application\Queries\ListCatalogs\ListCatalogsQuery;
use Commercial\Api\Requests\CreateCatalogRequest;
use Commercial\Api\Requests\UpdateCatalogRequest;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Illuminate\Http\Response;

class CatalogControllerTest extends MockeryTestCase
{
	private CatalogController $controller;
	private CommandBus $commandBus;
	private QueryBus $queryBus;

	protected function setUp(): void
	{
		parent::setUp();
		$this->commandBus = Mockery::mock(CommandBus::class);
		$this->queryBus = Mockery::mock(QueryBus::class);
		$this->controller = new CatalogController($this->commandBus, $this->queryBus);
	}

	public function testIndexReturnsListOfCatalogs(): void
	{
		$catalogs = [
			['id' => '1', 'nombre' => 'Catalog 1', 'estado' => 'activo'],
			['id' => '2', 'nombre' => 'Catalog 2', 'estado' => 'inactivo'],
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListCatalogsQuery::class))
			->once()
			->andReturn($catalogs);

		$response = $this->controller->index();

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals($catalogs, json_decode($response->getContent(), true));
	}

	public function testIndexHandlesException(): void
	{
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListCatalogsQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->index();

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al listar los catálogos: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testShowReturnsCatalog(): void
	{
		$catalogId = 'catalog-123';
		$catalog = [
			'id' => $catalogId,
			'nombre' => 'Test Catalog',
			'estado' => 'activo',
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andReturn($catalog);

		$response = $this->controller->show($catalogId);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals($catalog, json_decode($response->getContent(), true));
	}

	public function testShowHandlesCatalogException(): void
	{
		$catalogId = 'catalog-123';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andThrow(new CatalogException('Catálogo no encontrado'));

		$response = $this->controller->show($catalogId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Catálogo no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testShowHandlesGenericException(): void
	{
		$catalogId = 'catalog-123';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->show($catalogId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al obtener el catálogo: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testStoreCreatesCatalog(): void
	{
		$request = Mockery::mock(CreateCatalogRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Test Catalog');

		$commandResult = Mockery::mock('Commercial\Application\Commands\CommandResult');
		$commandResult->shouldReceive('isSuccess')->andReturn(true);
		$commandResult->shouldReceive('getId')->andReturn('catalog-123');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateCatalogCommand::class))
			->once()
			->andReturn($commandResult);

		$catalog = ['id' => 'catalog-123', 'nombre' => 'Test Catalog', 'estado' => 'activo'];
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andReturn($catalog);

		$response = $this->controller->store($request);

		$this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
		$this->assertEquals(
			'Catálogo creado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
	}

	public function testStoreHandlesException(): void
	{
		$request = Mockery::mock(CreateCatalogRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Test Catalog');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateCatalogCommand::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->store($request);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al crear el catálogo: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateModifiesCatalog(): void
	{
		$catalogId = 'catalog-123';
		$currentCatalog = (object) [
			'estado' => ServiceStatus::fromString('activo'),
		];

		$request = Mockery::mock(UpdateCatalogRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Updated Catalog');
		$request->shouldReceive('validated')->with('estado')->andReturn('inactivo');

		$commandResult = Mockery::mock('Commercial\Application\Commands\CommandResult');
		$commandResult->shouldReceive('isSuccess')->andReturn(true);
		$commandResult->shouldReceive('getId')->andReturn($catalogId);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andReturn($currentCatalog);

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateCatalogCommand::class))
			->once()
			->andReturn($commandResult);

		$updatedCatalog = [
			'id' => $catalogId,
			'nombre' => 'Updated Catalog',
			'estado' => 'inactivo',
		];
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andReturn($updatedCatalog);

		$response = $this->controller->update($request, $catalogId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(
			'Catálogo actualizado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
	}

	public function testUpdateHandlesCatalogException(): void
	{
		$catalogId = 'catalog-123';
		$request = Mockery::mock(UpdateCatalogRequest::class);
		$request->shouldReceive('all')->andReturn([
			'nombre' => 'Updated Catalog',
			'estado' => 'inactivo',
		]);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andThrow(new CatalogException('Catálogo no encontrado'));

		$response = $this->controller->update($request, $catalogId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Catálogo no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateHandlesGenericException(): void
	{
		$catalogId = 'catalog-123';
		$request = Mockery::mock(UpdateCatalogRequest::class);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetCatalogQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->update($request, $catalogId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al actualizar el catálogo: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testDestroyDeletesCatalog(): void
	{
		$catalogId = 'catalog-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(DeleteCatalogCommand::class))
			->once();

		$response = $this->controller->destroy($catalogId);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertEquals(
			'Catálogo eliminado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
	}

	public function testDestroyHandlesCatalogException(): void
	{
		$catalogId = 'catalog-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(DeleteCatalogCommand::class))
			->once()
			->andThrow(new CatalogException('Catálogo no encontrado'));

		$response = $this->controller->destroy($catalogId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Catálogo no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testDestroyHandlesGenericException(): void
	{
		$catalogId = 'catalog-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(DeleteCatalogCommand::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->destroy($catalogId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al eliminar el catálogo: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}
}
