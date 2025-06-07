<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateServiceStatus;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Application\Commands\CommandResult;

final class UpdateServiceStatusHandler
{
	public function __construct(private readonly ServiceRepository $serviceRepository) {}

	public function __invoke(UpdateServiceStatusCommand $command): CommandResult
	{
		$service = $this->serviceRepository->findById($command->getId());

		if (!$service) {
			throw CatalogException::serviceNotFound($command->getId());
		}

		$service->updateEstado($command->getEstado());
		$this->serviceRepository->save($service);

		return CommandResult::success(
			$command->getId(),
			'Estado del servicio actualizado exitosamente'
		);
	}

	public function handle(UpdateServiceStatusCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
