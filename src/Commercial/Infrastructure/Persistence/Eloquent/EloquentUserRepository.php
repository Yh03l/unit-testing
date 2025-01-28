<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\ValueObjects\Email;

class EloquentUserRepository implements UserRepository
{
    private UserModel $model;

    public function __construct(UserModel $model)
    {
        $this->model = $model;
    }

    public function findById(string $id): ?User
    {
        $userModel = $this->model->find($id);
        if (!$userModel) {
            return null;
        }

        return $this->toDomain($userModel);
    }

    public function save(User $user): void
    {
        $this->model->updateOrCreate(
            ['id' => $user->getId()],
            [
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'email' => $user->getEmail()->getValue(),
                'tipo_usuario_id' => $user->getTipoUsuarioId(),
                'estado' => $user->getEstado(),
            ]
        );
    }

    public function delete(string $id): void
    {
        $this->model->destroy($id);
    }

    public function findByEmail(string $email): ?User
    {
        $userModel = $this->model->where('email', $email)->first();
        if (!$userModel) {
            return null;
        }

        return $this->toDomain($userModel);
    }

    public function findAll(): array
    {
        return $this->model
            ->all()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    private function toDomain(UserModel $model): User
    {
        return User::create(
            $model->id,
            $model->nombre,
            $model->apellido,
            new Email($model->email),
            $model->tipo_usuario_id
        );
    }
} 