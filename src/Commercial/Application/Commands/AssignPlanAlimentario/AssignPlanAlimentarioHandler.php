<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\AssignPlanAlimentario;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Exceptions\ContractException;
use Commercial\Infrastructure\EventBus\EventBus;

class AssignPlanAlimentarioHandler
{
	public function __construct(
		private readonly ContractRepository $repository,
		private readonly ServiceRepository $serviceRepository,
		private readonly EventBus $eventBus
	) {}

	public function handle(AssignPlanAlimentarioCommand $command): void
	{
		$contract = $this->repository->findById($command->getContractId());

		if ($contract === null) {
			throw ContractException::notFound($command->getContractId());
		}

		// Verificar si el plan alimentario ya está asignado para evitar duplicación
		if ($contract->getPlanAlimentarioId() === $command->getPlanAlimentarioId()) {
			return; // Si ya está asignado, no hacemos nada
		}

		$contract->asignarPlanAlimentario($command->getPlanAlimentarioId());
		$this->repository->save($contract);

		// Publicar eventos
		foreach ($contract->getEvents() as $event) {
			$this->eventBus->publish($event);
		}
		$contract->clearEvents();
	}
}
