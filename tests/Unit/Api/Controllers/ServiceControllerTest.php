<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Controllers;

use Commercial\Api\Controllers\ServiceController;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Application\Commands\CreateService\CreateServiceCommand;
use Commercial\Application\Commands\UpdateService\UpdateServiceCommand;
use Commercial\Application\Commands\UpdateServiceStatus\UpdateServiceStatusCommand;
use Commercial\Application\Commands\UpdateServiceCost\UpdateServiceCostCommand;
use Commercial\Application\Queries\GetServiceDetails\GetServiceDetailsQuery;
use Commercial\Application\Queries\ListActiveServices\ListActiveServicesQuery;
use Commercial\Application\Queries\GetServiceCostHistory\GetServiceCostHistoryQuery;
use Commercial\Api\Requests\CreateServiceRequest;
use Commercial\Api\Requests\UpdateServiceRequest;
use Commercial\Api\Requests\UpdateServiceStatusRequest;
use Commercial\Api\Requests\UpdateServiceCostRequest;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Illuminate\Http\Response;
use DateTimeImmutable;
use Commercial\Application\Commands\CommandResult;

class ServiceControllerTest extends MockeryTestCase
{
	private ServiceController $controller;
	private CommandBus $commandBus;
	private QueryBus $queryBus;

	protected function setUp(): void
	{
		parent::setUp();
		$this->commandBus = Mockery::mock(CommandBus::class);
		$this->queryBus = Mockery::mock(QueryBus::class);
		$this->controller = new ServiceController($this->commandBus, $this->queryBus);
	}

	public function testIndexReturnsListOfServices(): void
	{
		$services = [
			[
				'id' => 'service-1',
				'nombre' => 'Service 1',
				'descripcion' => 'Description 1',
				'monto' => 100.0,
				'moneda' => 'BOB',
				'tipo_servicio_id' => 'asesoramiento',
				'estado' => 'activo',
				'catalogo_id' => 'catalog-1',
				'vigencia' => '2024-12-31',
			],
			[
				'id' => 'service-2',
				'nombre' => 'Service 2',
				'descripcion' => 'Description 2',
				'monto' => 200.0,
				'moneda' => 'USD',
				'tipo_servicio_id' => 'catering',
				'estado' => 'activo',
				'catalogo_id' => 'catalog-2',
				'vigencia' => '2024-12-31',
			],
		];

		$serviceDTOs = array_map(function ($service) {
			$dto = Mockery::mock();
			$dto->shouldReceive('toArray')->andReturn($service);
			return $dto;
		}, $services);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListActiveServicesQuery::class))
			->once()
			->andReturn($serviceDTOs);

		$response = $this->controller->index();

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals($services, json_decode($response->getContent(), true)['data']);
	}

	public function testIndexHandlesCatalogException(): void
	{
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListActiveServicesQuery::class))
			->once()
			->andThrow(new CatalogException('Error de catálogo'));

		$response = $this->controller->index();

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertEquals(
			'Error de catálogo',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testIndexHandlesGenericException(): void
	{
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(ListActiveServicesQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->index();

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error interno al listar los servicios',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testShowReturnsServiceDetails(): void
	{
		$serviceId = 'service-123';
		$serviceData = [
			'id' => 'service-123',
			'nombre' => 'Test Service',
			'descripcion' => 'Test Description',
			'monto' => 100.0,
			'moneda' => 'BOB',
			'tipo_servicio_id' => 'asesoramiento',
			'estado' => 'activo',
			'catalogo_id' => 'catalog-123',
			'vigencia' => '2024-12-31',
		];

		$serviceDTO = Mockery::mock();
		$serviceDTO->shouldReceive('toArray')->andReturn($serviceData);

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceDetailsQuery::class))
			->once()
			->andReturn($serviceDTO);

		$response = $this->controller->show($serviceId);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals($serviceData, json_decode($response->getContent(), true)['data']);
	}

	public function testShowHandlesCatalogException(): void
	{
		$serviceId = 'service-123';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceDetailsQuery::class))
			->once()
			->andThrow(new CatalogException('Servicio no encontrado'));

		$response = $this->controller->show($serviceId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Servicio no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testShowHandlesGenericException(): void
	{
		$serviceId = 'service-123';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceDetailsQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->show($serviceId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error interno al obtener el servicio',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testStoreCreatesNewService(): void
	{
		/** @var CreateServiceRequest&\Mockery\MockInterface */
		$request = Mockery::mock(CreateServiceRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Test Service');
		$request->shouldReceive('validated')->with('descripcion')->andReturn('Test Description');
		$request->shouldReceive('validated')->with('monto')->andReturn(100.0);
		$request->shouldReceive('validated')->with('moneda')->andReturn('BOB');
		$request->shouldReceive('validated')->with('vigencia')->andReturn('2024-12-31');
		$request->shouldReceive('validated')->with('tipo_servicio_id')->andReturn('asesoramiento');
		$request->shouldReceive('validated')->with('catalogo_id')->andReturn('catalog-123');

		$commandResult = Mockery::mock(CommandResult::class);
		$commandResult->shouldReceive('isSuccess')->andReturn(true);
		$commandResult->shouldReceive('getId')->andReturn('service-123');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateServiceCommand::class))
			->once()
			->andReturn($commandResult);

		// Mock para obtener el servicio creado
		$serviceData = [
			'id' => 'service-123',
			'nombre' => 'Test Service',
			'descripcion' => 'Test Description',
			'monto' => 100.0,
			'moneda' => 'BOB',
			'tipo_servicio_id' => 'asesoramiento',
			'estado' => 'activo',
			'catalogo_id' => 'catalog-123',
			'vigencia' => '2024-12-31',
		];
		$serviceDTO = Mockery::mock();
		$serviceDTO->shouldReceive('toArray')->andReturn($serviceData);
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceDetailsQuery::class))
			->andReturn($serviceDTO);

		$response = $this->controller->store($request);

		$this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
		$this->assertEquals(
			'Servicio creado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
		$this->assertEquals($serviceData, json_decode($response->getContent(), true)['data']);
	}

	public function testStoreHandlesCatalogException(): void
	{
		/** @var CreateServiceRequest&\Mockery\MockInterface */
		$request = Mockery::mock(CreateServiceRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Test Service');
		$request->shouldReceive('validated')->with('descripcion')->andReturn('Test Description');
		$request->shouldReceive('validated')->with('monto')->andReturn('100.00');
		$request->shouldReceive('validated')->with('moneda')->andReturn('BOB');
		$request->shouldReceive('validated')->with('vigencia')->andReturn('2024-12-31');
		$request->shouldReceive('validated')->with('tipo_servicio_id')->andReturn('asesoramiento');
		$request->shouldReceive('validated')->with('catalogo_id')->andReturn('catalog-456');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateServiceCommand::class))
			->once()
			->andThrow(new CatalogException('Error al crear el servicio'));

		$response = $this->controller->store($request);

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertEquals(
			'Error al crear el servicio',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testStoreHandlesInvalidArgumentException(): void
	{
		/** @var CreateServiceRequest&\Mockery\MockInterface */
		$request = Mockery::mock(CreateServiceRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Test Service');
		$request->shouldReceive('validated')->with('descripcion')->andReturn('Test Description');
		$request->shouldReceive('validated')->with('monto')->andReturn('100.00');
		$request->shouldReceive('validated')->with('moneda')->andReturn('BOB');
		$request->shouldReceive('validated')->with('vigencia')->andReturn('2024-12-31');
		$request->shouldReceive('validated')->with('tipo_servicio_id')->andReturn('asesoramiento');
		$request->shouldReceive('validated')->with('catalogo_id')->andReturn('catalog-456');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateServiceCommand::class))
			->once()
			->andThrow(new \InvalidArgumentException('Datos inválidos'));

		$response = $this->controller->store($request);

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
		$this->assertEquals('Datos inválidos', json_decode($response->getContent(), true)['error']);
	}

	public function testStoreHandlesGenericException(): void
	{
		/** @var CreateServiceRequest&\Mockery\MockInterface */
		$request = Mockery::mock(CreateServiceRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Test Service');
		$request->shouldReceive('validated')->with('descripcion')->andReturn('Test Description');
		$request->shouldReceive('validated')->with('monto')->andReturn('100.00');
		$request->shouldReceive('validated')->with('moneda')->andReturn('BOB');
		$request->shouldReceive('validated')->with('vigencia')->andReturn('2024-12-31');
		$request->shouldReceive('validated')->with('tipo_servicio_id')->andReturn('asesoramiento');
		$request->shouldReceive('validated')->with('catalogo_id')->andReturn('catalog-456');

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(CreateServiceCommand::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->store($request);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error interno al crear el servicio',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateModifiesExistingService(): void
	{
		/** @var UpdateServiceRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Updated Service');
		$request->shouldReceive('validated')->with('descripcion')->andReturn('Updated Description');
		$request->shouldReceive('validated')->with('tipo_servicio_id')->andReturn('asesoramiento');
		$request->shouldReceive('validated')->with('catalogo_id')->andReturn('catalog-123');
		$serviceId = 'service-123';

		$commandResult = Mockery::mock(CommandResult::class);
		$commandResult->shouldReceive('isSuccess')->andReturn(true);

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceCommand::class))
			->once()
			->andReturn($commandResult);

		// Mock para obtener el servicio actualizado
		$serviceData = [
			'id' => 'service-123',
			'nombre' => 'Updated Service',
			'descripcion' => 'Updated Description',
			'monto' => 100.0,
			'moneda' => 'BOB',
			'tipo_servicio_id' => 'asesoramiento',
			'estado' => 'activo',
			'catalogo_id' => 'catalog-123',
			'vigencia' => '2024-12-31',
		];
		$serviceDTO = Mockery::mock();
		$serviceDTO->shouldReceive('toArray')->andReturn($serviceData);
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceDetailsQuery::class))
			->andReturn($serviceDTO);

		$response = $this->controller->update($request, $serviceId);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(
			'Servicio actualizado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
		$this->assertEquals($serviceData, json_decode($response->getContent(), true)['data']);
	}

	public function testUpdateHandlesCatalogException(): void
	{
		/** @var UpdateServiceRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Updated Service');
		$request->shouldReceive('validated')->with('descripcion')->andReturn('Updated Description');
		$request->shouldReceive('validated')->with('tipo_servicio_id')->andReturn('asesoramiento');
		$request->shouldReceive('validated')->with('catalogo_id')->andReturn('catalog-123');
		$serviceId = 'service-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceCommand::class))
			->once()
			->andThrow(new CatalogException('Servicio no encontrado'));

		$response = $this->controller->update($request, $serviceId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Servicio no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateHandlesGenericException(): void
	{
		/** @var UpdateServiceRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceRequest::class);
		$request->shouldReceive('validated')->with('nombre')->andReturn('Updated Service');
		$request->shouldReceive('validated')->with('descripcion')->andReturn('Updated Description');
		$request->shouldReceive('validated')->with('tipo_servicio_id')->andReturn('asesoramiento');
		$request->shouldReceive('validated')->with('catalogo_id')->andReturn('catalog-123');
		$serviceId = 'service-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceCommand::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->update($request, $serviceId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al actualizar el servicio: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateStatusModifiesServiceStatus(): void
	{
		/** @var UpdateServiceStatusRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceStatusRequest::class);
		$request->shouldReceive('validated')->with('estado')->andReturn('inactivo');
		$serviceId = 'service-123';

		$commandResult = Mockery::mock(CommandResult::class);
		$commandResult->shouldReceive('isSuccess')->andReturn(true);

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceStatusCommand::class))
			->once()
			->andReturn($commandResult);

		// Mock para obtener el servicio actualizado
		$serviceData = [
			'id' => 'service-123',
			'nombre' => 'Test Service',
			'descripcion' => 'Test Description',
			'monto' => 100.0,
			'moneda' => 'BOB',
			'tipo_servicio_id' => 'asesoramiento',
			'estado' => 'inactivo',
			'catalogo_id' => 'catalog-123',
			'vigencia' => '2024-12-31',
		];
		$serviceDTO = Mockery::mock();
		$serviceDTO->shouldReceive('toArray')->andReturn($serviceData);
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceDetailsQuery::class))
			->andReturn($serviceDTO);

		$response = $this->controller->updateStatus($request, $serviceId);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(
			'Estado del servicio actualizado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
		$this->assertEquals($serviceData, json_decode($response->getContent(), true)['data']);
	}

	public function testUpdateStatusHandlesCatalogException(): void
	{
		/** @var UpdateServiceStatusRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceStatusRequest::class);
		$request->shouldReceive('validated')->with('estado')->andReturn('inactivo');
		$serviceId = 'service-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceStatusCommand::class))
			->once()
			->andThrow(new CatalogException('Servicio no encontrado'));

		$response = $this->controller->updateStatus($request, $serviceId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Servicio no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateStatusHandlesGenericException(): void
	{
		/** @var UpdateServiceStatusRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceStatusRequest::class);
		$request->shouldReceive('validated')->with('estado')->andReturn('inactivo');
		$serviceId = 'service-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceStatusCommand::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->updateStatus($request, $serviceId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al actualizar el estado del servicio: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateCostModifiesServiceCost(): void
	{
		/** @var UpdateServiceCostRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceCostRequest::class);
		$request->shouldReceive('validated')->with('monto')->andReturn(200.0);
		$request->shouldReceive('validated')->with('moneda')->andReturn('USD');
		$request->shouldReceive('validated')->with('vigencia')->andReturn('2024-12-31');
		$serviceId = 'service-123';

		$commandResult = Mockery::mock(CommandResult::class);
		$commandResult->shouldReceive('isSuccess')->andReturn(true);

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceCostCommand::class))
			->once()
			->andReturn($commandResult);

		// Mock para obtener el servicio actualizado
		$serviceData = [
			'id' => 'service-123',
			'nombre' => 'Test Service',
			'descripcion' => 'Test Description',
			'monto' => 200.0,
			'moneda' => 'USD',
			'tipo_servicio_id' => 'asesoramiento',
			'estado' => 'activo',
			'catalogo_id' => 'catalog-123',
			'vigencia' => '2024-12-31',
		];
		$serviceDTO = Mockery::mock();
		$serviceDTO->shouldReceive('toArray')->andReturn($serviceData);
		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceDetailsQuery::class))
			->andReturn($serviceDTO);

		$response = $this->controller->updateCost($request, $serviceId);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(
			'Costo del servicio actualizado exitosamente',
			json_decode($response->getContent(), true)['message']
		);
		$this->assertEquals($serviceData, json_decode($response->getContent(), true)['data']);
	}

	public function testUpdateCostHandlesCatalogException(): void
	{
		/** @var UpdateServiceCostRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceCostRequest::class);
		$request->shouldReceive('validated')->with('monto')->andReturn(200.0);
		$request->shouldReceive('validated')->with('moneda')->andReturn('USD');
		$request->shouldReceive('validated')->with('vigencia')->andReturn('2024-12-31');
		$serviceId = 'service-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceCostCommand::class))
			->once()
			->andThrow(new CatalogException('Servicio no encontrado'));

		$response = $this->controller->updateCost($request, $serviceId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Servicio no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testUpdateCostHandlesGenericException(): void
	{
		/** @var UpdateServiceCostRequest&\Mockery\MockInterface */
		$request = Mockery::mock(UpdateServiceCostRequest::class);
		$request->shouldReceive('validated')->with('monto')->andReturn(200.0);
		$request->shouldReceive('validated')->with('moneda')->andReturn('USD');
		$request->shouldReceive('validated')->with('vigencia')->andReturn('2024-12-31');
		$serviceId = 'service-123';

		$this->commandBus
			->shouldReceive('dispatch')
			->with(Mockery::type(UpdateServiceCostCommand::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->updateCost($request, $serviceId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al actualizar el costo del servicio: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testGetCostHistoryReturnsHistory(): void
	{
		$serviceId = 'service-123';
		$history = [
			['monto' => 100.0, 'moneda' => 'BOB', 'vigencia' => '2024-01-01'],
			['monto' => 150.0, 'moneda' => 'BOB', 'vigencia' => '2024-12-31'],
		];

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceCostHistoryQuery::class))
			->once()
			->andReturn($history);

		$response = $this->controller->getCostHistory($serviceId);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals($history, json_decode($response->getContent(), true)['data']);
	}

	public function testGetCostHistoryHandlesCatalogException(): void
	{
		$serviceId = 'service-123';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceCostHistoryQuery::class))
			->once()
			->andThrow(new CatalogException('Servicio no encontrado'));

		$response = $this->controller->getCostHistory($serviceId);

		$this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertEquals(
			'Servicio no encontrado',
			json_decode($response->getContent(), true)['error']
		);
	}

	public function testGetCostHistoryHandlesGenericException(): void
	{
		$serviceId = 'service-123';

		$this->queryBus
			->shouldReceive('dispatch')
			->with(Mockery::type(GetServiceCostHistoryQuery::class))
			->once()
			->andThrow(new \Exception('Error interno'));

		$response = $this->controller->getCostHistory($serviceId);

		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals(
			'Error al obtener el historial de costos: Error interno',
			json_decode($response->getContent(), true)['error']
		);
	}
}
