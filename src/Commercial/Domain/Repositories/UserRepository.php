<?php

declare(strict_types=1);

namespace Commercial\Domain\Repositories;

use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\ValueObjects\Email;

interface UserRepository
{
	public function save(User $user): void;
	public function findByEmail(Email $email): ?User;
	public function findById(string $id): ?User;
	public function findAll(): array;
	public function findAllPatients(?int $limit = null, ?int $offset = null): array;
	public function delete(string $id): void;
}
