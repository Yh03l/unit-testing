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
	private ContractRepository $repository;
	private EventBus $eventBus;

	public function __construct(ContractRepository $repository, EventBus $eventBus)
	{
		$this->repository = $repository;
		$this->eventBus = $eventBus;
	}

	public function __invoke(CreateContractCommand $command): CommandResult
	{
		return $this->handle($command);
	}

	public function handle(CreateContractCommand $command): CommandResult
	{
		$contractDate = new ContractDate($command->getFechaInicio(), $command->getFechaFin());

		$contract = Contract::create(
			(string) Str::uuid(),
			$command->getPacienteId(),
			$command->getServicioId(),
			$contractDate
		);

		$contractId = $this->repository->save($contract);

		// Publicar eventos
		foreach ($contract->getEvents() as $event) {
			$this->eventBus->publish($event);
		}
		$contract->clearEvents();

		return CommandResult::success($contractId, 'Contrato creado exitosamente');
	}
}
