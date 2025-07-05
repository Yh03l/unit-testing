<?php

declare(strict_types=1);

namespace Commercial\Api\Controllers;

use Commercial\Application\Commands\CreatePatient\CreatePatientCommand;
use Commercial\Application\Queries\GetPatientByEmail\GetPatientByEmailQuery;
use Commercial\Application\Queries\GetPatientById\GetPatientByIdQuery;
use Commercial\Application\Queries\ListPatients\ListPatientsQuery;
use Commercial\Api\Requests\CreatePatientRequest;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class PatientController extends Controller
{
	public function __construct(
		private readonly CommandBus $commandBus,
		private readonly QueryBus $queryBus
	) {}

	public function create(CreatePatientRequest $request): JsonResponse
	{
		try {
			$command = new CreatePatientCommand(
				nombre: $request->validated('nombre'),
				apellido: $request->validated('apellido'),
				email: $request->validated('email'),
				fechaNacimiento: $request->validated('fecha_nacimiento'),
				genero: $request->validated('genero'),
				direccion: $request->validated('direccion'),
				telefono: $request->validated('telefono')
			);

			$result = $this->commandBus->dispatch($command);

			if (!$result->isSuccess()) {
				return new JsonResponse(
					['error' => $result->getMessage()],
					Response::HTTP_BAD_REQUEST
				);
			}

			// Obtener los datos del paciente creado
			$patient = $this->queryBus->dispatch(
				new GetPatientByEmailQuery($request->validated('email'))
			);

			return new JsonResponse(
				[
					'message' => 'Paciente creado exitosamente',
					'data' => $patient,
				],
				Response::HTTP_CREATED
			);
		} catch (\DomainException $e) {
			return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error interno al crear el paciente: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function get(string $id): JsonResponse
	{
		try {
			$patient = $this->queryBus->dispatch(new GetPatientByIdQuery($id));

			if (!$patient) {
				return new JsonResponse(
					['error' => 'Paciente no encontrado'],
					Response::HTTP_NOT_FOUND
				);
			}

			return new JsonResponse(['data' => $patient]);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error interno al obtener el paciente: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}

	public function list(Request $request): JsonResponse
	{
		try {
			$limit = $request->input('limit', 10);
			$offset = $request->input('offset', 0);

			$patients = $this->queryBus->dispatch(new ListPatientsQuery($limit, $offset));

			return new JsonResponse(['data' => $patients]);
		} catch (\Exception $e) {
			return new JsonResponse(
				['error' => 'Error interno al listar los pacientes: ' . $e->getMessage()],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}
	}
}
