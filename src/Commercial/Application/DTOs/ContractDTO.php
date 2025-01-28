<?php

declare(strict_types=1);

namespace Commercial\Application\DTOs;

class ContractDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $pacienteId,
        public readonly string $servicioId,
        public readonly \DateTimeImmutable $fechaInicio,
        public readonly ?\DateTimeImmutable $fechaFin,
        public readonly string $estado
    ) {
    }
} 