<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListContractsByPaciente;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Application\DTOs\ContractDTO;

class ListContractsByPacienteHandler
{
    private ContractRepository $repository;

    public function __construct(ContractRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return ContractDTO[]
     */
    public function __invoke(ListContractsByPacienteQuery $query): array
    {
        return $this->handle($query);
    }

    /**
     * @return ContractDTO[]
     */
    public function handle(ListContractsByPacienteQuery $query): array
    {
        $contracts = $this->repository->findByPacienteId($query->getPacienteId());

        return array_map(
            fn($contract) => new ContractDTO(
                $contract->getId(),
                $contract->getPacienteId(),
                $contract->getServicioId(),
                $contract->getFechaContrato()->getFechaInicio(),
                $contract->getFechaContrato()->getFechaFin(),
                $contract->getEstado()
            ),
            $contracts
        );
    }
} 