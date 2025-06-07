<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\ActivateContract;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Application\Commands\CommandResult;

class ActivateContractHandler
{
	public function __construct(
		private readonly ContractRepository $repository,
		private readonly EventBus $eventBus
	) {}

	public function __invoke(ActivateContractCommand $command): CommandResult
	{
		$contract = $this->repository->findById($command->getContractId());
		if ($contract === null) {
			throw new \DomainException('Contrato no encontrado');
		}

		$contract->activarContrato();
		$this->repository->save($contract);

		// Publicar eventos
		foreach ($contract->getEvents() as $event) {
			$this->eventBus->publish($event);
		}
		$contract->clearEvents();

		return CommandResult::success($command->getContractId(), 'Contrato activado exitosamente');
	}

	public function handle(ActivateContractCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
