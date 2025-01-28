<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\ActivateContract;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Infrastructure\EventBus\EventBus;

class ActivateContractHandler
{
    private ContractRepository $repository;
    private EventBus $eventBus;

    public function __construct(ContractRepository $repository, EventBus $eventBus)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(ActivateContractCommand $command): void
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
    }
} 