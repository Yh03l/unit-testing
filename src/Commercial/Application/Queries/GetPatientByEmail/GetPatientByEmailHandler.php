<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetPatientByEmail;

use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\ValueObjects\Email;
use Commercial\Application\DTOs\UserDTO;

class GetPatientByEmailHandler
{
	public function __construct(private readonly UserRepository $userRepository) {}

	public function __invoke(GetPatientByEmailQuery $query): ?array
	{
		$email = new Email($query->email);
		$patient = $this->userRepository->findByEmail($email);

		if (!$patient) {
			return null;
		}

		$dto = UserDTO::fromEntity($patient);
		return $dto->toArray();
	}
}
