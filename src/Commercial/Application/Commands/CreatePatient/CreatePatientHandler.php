<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreatePatient;

use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\Events\PatientCreated;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\ValueObjects\Email;
use Commercial\Application\Commands\CommandResult;
use Commercial\Infrastructure\EventBus\EventBus;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class CreatePatientHandler
{
	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly EventBus $eventBus
	) {}

	public function __invoke(CreatePatientCommand $command): CommandResult
	{
		try {
			$email = new Email($command->email);

			// Verificar si ya existe un usuario con este email
			$existingUser = $this->userRepository->findByEmail($email);
			if ($existingUser) {
				return CommandResult::failure(
					sprintf('Ya existe un usuario con el email %s', $command->email)
				);
			}

			$id = Uuid::uuid4()->toString();
			$fechaNacimiento = new DateTimeImmutable($command->fechaNacimiento);

			$patient = new Patient(
				id: $id,
				nombre: $command->nombre,
				apellido: $command->apellido,
				email: $email,
				estado: 'activo',
				fechaNacimiento: $fechaNacimiento,
				genero: $command->genero,
				direccion: $command->direccion,
				telefono: $command->telefono
			);

			// Agregar el evento de dominio
			$event = new PatientCreated(
				id: $id,
				email: $email->toString(),
				nombre: $command->nombre,
				apellido: $command->apellido,
				fechaNacimiento: $fechaNacimiento,
				genero: $command->genero,
				direccion: $command->direccion,
				telefono: $command->telefono
			);

			$patient->addEvent($event);
			$this->userRepository->save($patient);

			// Publicar el evento al broker
			$this->eventBus->publish($event);

			return CommandResult::success($id, 'Paciente creado exitosamente');
		} catch (\Exception $e) {
			return CommandResult::failure($e->getMessage());
		}
	}
}
