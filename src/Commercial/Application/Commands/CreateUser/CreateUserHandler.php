<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateUser;

use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\Aggregates\User\Administrator;
use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\ValueObjects\Email;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Application\Commands\CommandResult;
use Commercial\Domain\Events\UserCreated;
use DateTimeImmutable;
use Illuminate\Support\Str;

class CreateUserHandler
{
	public function __construct(
		private readonly UserRepository $repository,
		private readonly EventBus $eventBus
	) {}

	public function __invoke(CreateUserCommand $command): CommandResult
	{
		// Verificar si ya existe un usuario con ese email
		$existingUser = $this->repository->findByEmail(new Email($command->getEmail()));
		if ($existingUser !== null) {
			throw new \DomainException('Ya existe un usuario con ese email');
		}

		$userId = (string) Str::uuid();
		$email = new Email($command->getEmail());

		$user = match ($command->getTipoUsuarioId()) {
			'ADMINISTRADOR' => new Administrator(
				$userId,
				$command->getNombre(),
				$command->getApellido(),
				$email,
				'activo',
				$command->getCargo() ?? 'Sin cargo',
				$command->getDepartamento() ?? 'Sin departamento'
			),
			'PACIENTE' => new Patient(
				$userId,
				$command->getNombre(),
				$command->getApellido(),
				$email,
				'activo',
				new DateTimeImmutable($command->getFechaNacimiento() ?? 'now'),
				$command->getGenero() ?? 'No especificado',
				$command->getDireccion(),
				$command->getTelefono()
			),
			default => throw new \DomainException('Tipo de usuario no válido'),
		};

		$this->repository->save($user);

		// Publicar evento de creación
		$event = new UserCreated($userId, $command->getEmail());
		$this->eventBus->publish($event);

		return CommandResult::success($userId, 'Usuario creado exitosamente');
	}

	public function handle(CreateUserCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
