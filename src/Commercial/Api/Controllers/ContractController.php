<?php

declare(strict_types=1);

namespace Commercial\Api\Controllers;

use Commercial\Application\Commands\CreateContract\CreateContractCommand;
use Commercial\Application\Commands\ActivateContract\ActivateContractCommand;
use Commercial\Application\Commands\CancelContract\CancelContractCommand;
use Commercial\Application\Queries\GetContract\GetContractQuery;
use Commercial\Application\Queries\ListContractsByPaciente\ListContractsByPacienteQuery;
use Commercial\Api\Requests\CreateContractRequest;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ContractController extends Controller
{
	public function __construct(
		private readonly CommandBus $commandBus,
		private readonly QueryBus $queryBus
	) {}

	public function create(CreateContractRequest $request): JsonResponse
	{
		$command = new CreateContractCommand(
			$request->validated('paciente_id'),
			$request->validated('servicio_id'),
			$request->validated('fecha_inicio')
				? new \DateTimeImmutable($request->validated('fecha_inicio'))
				: null,
			$request->validated('fecha_fin')
				? new \DateTimeImmutable($request->validated('fecha_fin'))
				: null,
			$request->validated('plan_alimentario_id')
		);

		try {
			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			// Obtener los datos completos del contrato creado
			$contract = $this->queryBus->dispatch(new GetContractQuery($result->getId()));

			return new JsonResponse(
				[
					'message' => $result->getMessage(),
					'data' => $contract,
				],
				Response::HTTP_CREATED
			);
		} catch (\DomainException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
	}

	public function get(string $id): JsonResponse
	{
		$contract = $this->queryBus->dispatch(new GetContractQuery($id));

		if (!$contract) {
			return new JsonResponse(
				['error' => 'Contrato no encontrado'],
				Response::HTTP_NOT_FOUND
			);
		}

		return new JsonResponse(['data' => $contract]);
	}

	public function activate(string $id): JsonResponse
	{
		try {
			$result = $this->commandBus->dispatch(new ActivateContractCommand($id));

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			return new JsonResponse(['message' => $result->getMessage()]);
		} catch (\DomainException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
	}

	public function cancel(string $id): JsonResponse
	{
		try {
			$result = $this->commandBus->dispatch(new CancelContractCommand($id));

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			return new JsonResponse(['message' => $result->getMessage()]);
		} catch (\DomainException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
	}

	public function getByPaciente(string $pacienteId): JsonResponse
	{
		$contracts = $this->queryBus->dispatch(new ListContractsByPacienteQuery($pacienteId));

		return new JsonResponse(['data' => $contracts]);
	}
}
