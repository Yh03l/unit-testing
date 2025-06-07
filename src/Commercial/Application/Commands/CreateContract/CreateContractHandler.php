<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateContract;

use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\ValueObjects\ContractDate;
use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Application\Commands\CommandResult;
use Illuminate\Support\Str;

class CreateContractHandler
{
	public function __construct(
		private readonly ContractRepository $repository,
		private readonly EventBus $eventBus
	) {}

	public function __invoke(CreateContractCommand $command): CommandResult
	{
		$contractDate = new ContractDate($command->getFechaInicio(), $command->getFechaFin());
		$contractId = (string) Str::uuid();

		$contract = Contract::create(
			$contractId,
			$command->getPacienteId(),
			$command->getServicioId(),
			$contractDate
		);

		$this->repository->save($contract);

		// Publicar eventos
		foreach ($contract->getEvents() as $event) {
			$this->eventBus->publish($event);
		}
		$contract->clearEvents();

		return CommandResult::success($contractId, 'Contrato creado exitosamente');
	}

	public function handle(CreateContractCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
