<?php

declare(strict_types=1);

namespace Commercial\Api\Controllers;

use Commercial\Application\Commands\CreateUser\CreateUserCommand;
use Commercial\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use Commercial\Api\Requests\CreateUserRequest;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    private CommandBus $commandBus;
    private QueryBus $queryBus;

    public function __construct(CommandBus $commandBus, QueryBus $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function create(CreateUserRequest $request): JsonResponse
    {
        $command = new CreateUserCommand(
            $request->getNombre(),
            $request->getApellido(),
            $request->getEmail(),
            $request->getTipoUsuarioId()
        );

        try {
            $this->commandBus->dispatch($command);
            return new JsonResponse(['message' => 'Usuario creado exitosamente'], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getByEmail(string $email): JsonResponse
    {
        $query = new GetUserByEmailQuery($email);

        try {
            $user = $this->queryBus->ask($query);
            if ($user === null) {
                return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
            }
            return new JsonResponse($user, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 