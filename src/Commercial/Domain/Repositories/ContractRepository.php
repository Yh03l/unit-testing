<?php

declare(strict_types=1);

namespace Commercial\Domain\Repositories;

use Commercial\Domain\Aggregates\Contract\Contract;

interface ContractRepository
{
    public function findById(string $id): ?Contract;
    public function save(Contract $contract): void;
    public function delete(string $id): void;
    public function findByPacienteId(string $pacienteId): array;
    public function findAll(): array;
} 