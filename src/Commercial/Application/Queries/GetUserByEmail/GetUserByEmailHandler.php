<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetUserByEmail;

use Commercial\Domain\Repositories\UserRepository;
use Commercial\Application\DTOs\UserDTO;

final class GetUserByEmailHandler
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    public function __invoke(GetUserByEmailQuery $query): ?UserDTO
    {
        return $this->handle($query);
    }

    public function handle(GetUserByEmailQuery $query): ?UserDTO
    {
        $user = $this->repository->findByEmail($query->getEmail());

        if ($user === null) {
            return null;
        }

        return UserDTO::fromEntity($user);
    }
} 