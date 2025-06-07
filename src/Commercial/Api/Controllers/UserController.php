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
	public function __construct(
		private readonly CommandBus $commandBus,
		private readonly QueryBus $queryBus
	) {}

	public function create(CreateUserRequest $request): JsonResponse
	{
		try {
			$command = new CreateUserCommand(
				nombre: $request->validated('nombre'),
				apellido: $request->validated('apellido'),
				email: $request->validated('email'),
				tipoUsuarioId: $request->validated('tipo_usuario_id')
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			// Obtener los datos del usuario creado
			$user = $this->queryBus->dispatch(
				new GetUserByEmailQuery($request->validated('email'))
			);

			return new JsonResponse(
				[
					'message' => 'Usuario creado exitosamente',
					'data' => $user,
				],
				Response::HTTP_CREATED
			);
		} catch (\DomainException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
	}

	public function getByEmail(string $email): JsonResponse
	{
		try {
			$user = $this->queryBus->dispatch(new GetUserByEmailQuery($email));

			if ($user === null) {
				return new JsonResponse(
					['error' => 'Usuario no encontrado'],
					Response::HTTP_NOT_FOUND
				);
			}

			return new JsonResponse(['data' => $user], Response::HTTP_OK);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}
}
