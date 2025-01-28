<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\ValueObjects\ContractDate;

class EloquentContractRepository implements ContractRepository
{
    private ContractModel $model;

    public function __construct(ContractModel $model)
    {
        $this->model = $model;
    }

    public function findById(string $id): ?Contract
    {
        $contractModel = $this->model->find($id);
        if (!$contractModel) {
            return null;
        }

        return $this->toDomain($contractModel);
    }

    public function save(Contract $contract): void
    {
        $this->model->updateOrCreate(
            ['id' => $contract->getId()],
            [
                'paciente_id' => $contract->getPacienteId(),
                'servicio_id' => $contract->getServicioId(),
                'estado' => $contract->getEstado(),
                'fecha_inicio' => $contract->getFechaContrato()->getFechaInicio(),
                'fecha_fin' => $contract->getFechaContrato()->getFechaFin(),
            ]
        );
    }

    public function delete(string $id): void
    {
        $this->model->destroy($id);
    }

    public function findByPacienteId(string $pacienteId): array
    {
        return $this->model
            ->where('paciente_id', $pacienteId)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findAll(): array
    {
        return $this->model
            ->all()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    private function toDomain(ContractModel $model): Contract
    {
        return Contract::create(
            $model->id,
            $model->paciente_id,
            $model->servicio_id,
            new ContractDate(
                \DateTimeImmutable::createFromMutable($model->fecha_inicio),
                $model->fecha_fin ? \DateTimeImmutable::createFromMutable($model->fecha_fin) : null
            )
        );
    }
} 