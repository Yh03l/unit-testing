<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CancelContract;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Infrastructure\EventBus\EventBus;

class CancelContractHandler
{
    private ContractRepository $repository;
    private EventBus $eventBus;

    public function __construct(ContractRepository $repository, EventBus $eventBus)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(CancelContractCommand $command): void
    {
        $contract = $this->repository->findById($command->getContractId());
        if ($contract === null) {
            throw new \DomainException('Contrato no encontrado');
        }

        $contract->cancelarContrato();
        $this->repository->save($contract);

        // Publicar eventos
        foreach ($contract->getEvents() as $event) {
            $this->eventBus->publish($event);
        }
        $contract->clearEvents();
    }
} 