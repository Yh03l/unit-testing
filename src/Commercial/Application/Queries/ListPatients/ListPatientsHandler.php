<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListPatients;

use Commercial\Domain\Repositories\UserRepository;
use Commercial\Application\DTOs\UserDTO;

class ListPatientsHandler
{
	public function __construct(private readonly UserRepository $userRepository) {}

	public function __invoke(ListPatientsQuery $query): array
	{
		$patients = $this->userRepository->findAllPatients($query->limit, $query->offset);

		return array_map(fn($patient) => UserDTO::fromEntity($patient)->toArray(), $patients);
	}
}
