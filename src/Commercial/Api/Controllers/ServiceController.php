<?php

declare(strict_types=1);

namespace Commercial\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Commercial\Application\Commands\CreateService\CreateServiceCommand;
use Commercial\Application\Commands\UpdateService\UpdateServiceCommand;
use Commercial\Application\Commands\UpdateServiceStatus\UpdateServiceStatusCommand;
use Commercial\Application\Commands\UpdateServiceCost\UpdateServiceCostCommand;
use Commercial\Application\Queries\GetServiceDetails\GetServiceDetailsQuery;
use Commercial\Application\Queries\ListActiveServices\ListActiveServicesQuery;
use Commercial\Application\Queries\GetServiceCostHistory\GetServiceCostHistoryQuery;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Api\Requests\CreateServiceRequest;
use Commercial\Api\Requests\UpdateServiceRequest;
use Commercial\Api\Requests\UpdateServiceStatusRequest;
use Commercial\Api\Requests\UpdateServiceCostRequest;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Illuminate\Http\Response;
use DateTimeImmutable;

class ServiceController
{
	public function __construct(
		private readonly CommandBus $commandBus,
		private readonly QueryBus $queryBus
	) {}

	public function index(): JsonResponse
	{
		try {
			$services = $this->queryBus->dispatch(new ListActiveServicesQuery());
			return new JsonResponse([
				'data' => array_map(fn($service) => $service->toArray(), $services),
			]);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error interno al listar los servicios'],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function show(string $id): JsonResponse
	{
		try {
			$service = $this->queryBus->dispatch(new GetServiceDetailsQuery($id));
			return new JsonResponse(['data' => $service->toArray()]);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error interno al obtener el servicio'],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function store(CreateServiceRequest $request): JsonResponse
	{
		try {
			$command = new CreateServiceCommand(
				nombre: $request->validated('nombre'),
				descripcion: $request->validated('descripcion'),
				monto: (float) $request->validated('monto'),
				moneda: $request->validated('moneda'),
				vigencia: new DateTimeImmutable($request->validated('vigencia')),
				tipo_servicio_id: $request->validated('tipo_servicio_id'),
				catalogo_id: $request->validated('catalogo_id')
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			$service = $this->queryBus->dispatch(new GetServiceDetailsQuery($result->getId()));

			return new JsonResponse(
				[
					'message' => 'Servicio creado exitosamente',
					'data' => $service->toArray(),
				],
				Response::HTTP_CREATED
			);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		} catch (\InvalidArgumentException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error interno al crear el servicio', 'message' => $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function update(UpdateServiceRequest $request, string $id): JsonResponse
	{
		try {
			$command = new UpdateServiceCommand(
				id: $id,
				nombre: $request->validated('nombre'),
				descripcion: $request->validated('descripcion')
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			$service = $this->queryBus->dispatch(new GetServiceDetailsQuery($id));

			return new JsonResponse([
				'message' => 'Servicio actualizado exitosamente',
				'data' => $service->toArray(),
			]);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al actualizar el servicio: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function updateStatus(UpdateServiceStatusRequest $request, string $id): JsonResponse
	{
		try {
			$command = new UpdateServiceStatusCommand(
				id: $id,
				estado: ServiceStatus::fromString($request->validated('estado'))
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			$service = $this->queryBus->dispatch(new GetServiceDetailsQuery($id));

			return new JsonResponse([
				'message' => 'Estado del servicio actualizado exitosamente',
				'data' => $service->toArray(),
			]);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al actualizar el estado del servicio: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function updateCost(UpdateServiceCostRequest $request, string $id): JsonResponse
	{
		try {
			$command = new UpdateServiceCostCommand(
				id: $id,
				monto: (float) $request->validated('monto'),
				moneda: $request->validated('moneda'),
				vigencia: new DateTimeImmutable($request->validated('vigencia'))
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			$service = $this->queryBus->dispatch(new GetServiceDetailsQuery($id));

			return new JsonResponse([
				'message' => 'Costo del servicio actualizado exitosamente',
				'data' => $service->toArray(),
			]);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al actualizar el costo del servicio: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function getCostHistory(string $id): JsonResponse
	{
		try {
			$history = $this->queryBus->dispatch(new GetServiceCostHistoryQuery($id));
			return new JsonResponse(['data' => $history]);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al obtener el historial de costos: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}
}
