<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\ListContracts;

use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Application\DTOs\ContractDTO;

class ListContractsHandler
{
	public function __construct(private readonly ContractRepository $repository) {}

	public function __invoke(ListContractsQuery $query): array
	{
		$contracts = [];

		if ($query->pacienteId !== null) {
			// Filtrar por paciente específico
			$contracts = $this->repository->findByPacienteId($query->pacienteId);
		} else {
			// Obtener todos los contratos
			$contracts = $this->repository->findAll();
		}

		// Aplicar paginación si se especifica
		if ($query->limit !== null || $query->offset !== null) {
			$contracts = array_slice($contracts, $query->offset ?? 0, $query->limit);
		}

		return array_map(
			fn($contract) => new ContractDTO(
				$contract->getId(),
				$contract->getPacienteId(),
				$contract->getServicioId(),
				$contract->getPlanAlimentarioId(),
				$contract->getFechaContrato()->getFechaInicio(),
				$contract->getFechaContrato()->getFechaFin(),
				$contract->getEstado()
			),
			$contracts
		);
	}
}
