<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetContract;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Application\DTOs\ContractDTO;

class GetContractHandler
{
    private ContractRepository $repository;

    public function __construct(ContractRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(GetContractQuery $query): ?ContractDTO
    {
        $contract = $this->repository->findById($query->getContractId());

        if ($contract === null) {
            return null;
        }

        return new ContractDTO(
            $contract->getId(),
            $contract->getPacienteId(),
            $contract->getServicioId(),
            $contract->getFechaContrato()->getFechaInicio(),
            $contract->getFechaContrato()->getFechaFin(),
            $contract->getEstado()
        );
    }
} 