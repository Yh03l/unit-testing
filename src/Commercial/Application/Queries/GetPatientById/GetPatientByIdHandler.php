<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetPatientById;

use Commercial\Domain\Repositories\UserRepository;
use Commercial\Application\DTOs\UserDTO;

class GetPatientByIdHandler
{
	public function __construct(private readonly UserRepository $userRepository) {}

	public function __invoke(GetPatientByIdQuery $query): ?array
	{
		$patient = $this->userRepository->findById($query->id);

		if (!$patient) {
			return null;
		}

		$dto = UserDTO::fromEntity($patient);
		return $dto->toArray();
	}
}
