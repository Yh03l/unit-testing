<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateUser;

use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\ValueObjects\Email;
use Commercial\Infrastructure\EventBus\EventBus;
use Illuminate\Support\Str;

class CreateUserHandler
{
    private UserRepository $repository;
    private EventBus $eventBus;

    public function __construct(UserRepository $repository, EventBus $eventBus)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(CreateUserCommand $command): void
    {
        // Verificar si ya existe un usuario con ese email
        $existingUser = $this->repository->findByEmail($command->getEmail());
        if ($existingUser !== null) {
            throw new \DomainException('Ya existe un usuario con ese email');
        }

        $user = User::create(
            (string) Str::uuid(),
            $command->getNombre(),
            $command->getApellido(),
            new Email($command->getEmail()),
            $command->getTipoUsuarioId()
        );

        $this->repository->save($user);

        // Publicar eventos
        foreach ($user->getEvents() as $event) {
            $this->eventBus->publish($event);
        }
        $user->clearEvents();
    }
} 