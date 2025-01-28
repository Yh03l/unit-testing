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
    private CommandBus $commandBus;
    private QueryBus $queryBus;

    public function __construct(CommandBus $commandBus, QueryBus $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function create(CreateContractRequest $request): JsonResponse
    {
        $command = new CreateContractCommand(
            $request->getPacienteId(),
            $request->getServicioId(),
            $request->getFechaInicio(),
            $request->getFechaFin()
        );

        try {
            $this->commandBus->dispatch($command);
            return new JsonResponse(['message' => 'Contrato creado exitosamente'], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function get(string $id): JsonResponse
    {
        $query = new GetContractQuery($id);

        try {
            $contract = $this->queryBus->ask($query);
            if ($contract === null) {
                return new JsonResponse(['error' => 'Contrato no encontrado'], Response::HTTP_NOT_FOUND);
            }
            return new JsonResponse($contract, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function activate(string $id): JsonResponse
    {
        $command = new ActivateContractCommand($id);

        try {
            $this->commandBus->dispatch($command);
            return new JsonResponse(['message' => 'Contrato activado exitosamente'], Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancel(string $id): JsonResponse
    {
        $command = new CancelContractCommand($id);

        try {
            $this->commandBus->dispatch($command);
            return new JsonResponse(['message' => 'Contrato cancelado exitosamente'], Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getByPaciente(string $pacienteId): JsonResponse
    {
        $query = new ListContractsByPacienteQuery($pacienteId);

        try {
            $contracts = $this->queryBus->ask($query);
            return new JsonResponse($contracts, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 