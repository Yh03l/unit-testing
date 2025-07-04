<?php

declare(strict_types=1);

namespace Commercial\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Commercial\Application\Commands\CreateCatalog\CreateCatalogCommand;
use Commercial\Application\Commands\UpdateCatalog\UpdateCatalogCommand;
use Commercial\Application\Commands\DeleteCatalog\DeleteCatalogCommand;
use Commercial\Application\Queries\GetCatalog\GetCatalogQuery;
use Commercial\Application\Queries\ListCatalogs\ListCatalogsQuery;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Api\Requests\CreateCatalogRequest;
use Commercial\Api\Requests\UpdateCatalogRequest;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Illuminate\Http\Response;

class CatalogController
{
	public function __construct(
		private readonly CommandBus $commandBus,
		private readonly QueryBus $queryBus
	) {}

	public function index(): JsonResponse
	{
		try {
			$catalogs = $this->queryBus->dispatch(new ListCatalogsQuery());
			return new JsonResponse($catalogs);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al listar los catálogos: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function show(string $id): JsonResponse
	{
		try {
			$catalog = $this->queryBus->dispatch(new GetCatalogQuery($id));
			return new JsonResponse($catalog);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al obtener el catálogo: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function store(CreateCatalogRequest $request): JsonResponse
	{
		try {
			$command = new CreateCatalogCommand(
				nombre: $request->validated('nombre'),
				estado: ServiceStatus::fromString('activo')
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			$catalog = $this->queryBus->dispatch(new GetCatalogQuery($result->getId()));

			return new JsonResponse(
				[
					'message' => 'Catálogo creado exitosamente',
					'data' => $catalog,
				],
				Response::HTTP_CREATED
			);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al crear el catálogo: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function update(UpdateCatalogRequest $request, string $id): JsonResponse
	{
		try {
			$currentCatalog = $this->queryBus->dispatch(new GetCatalogQuery($id));

			$command = new UpdateCatalogCommand(
				id: $id,
				nombre: $request->validated('nombre'),
				estado: $request->validated('estado')
					? ServiceStatus::fromString($request->validated('estado'))
					: $currentCatalog->estado
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			$catalog = $this->queryBus->dispatch(new GetCatalogQuery($id));

			return new JsonResponse(
				['message' => 'Catálogo actualizado exitosamente', 'data' => $catalog],
				Response::HTTP_OK
			);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al actualizar el catálogo: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function destroy(string $id): JsonResponse
	{
		try {
			$this->commandBus->dispatch(new DeleteCatalogCommand($id));
			return new JsonResponse(['message' => 'Catálogo eliminado exitosamente']);
		} catch (CatalogException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error al eliminar el catálogo: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}
}
