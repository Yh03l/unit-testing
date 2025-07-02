<?php

declare(strict_types=1);

namespace Commercial\Domain\ValueObjects;

use Commercial\Domain\Helpers\DateTimeHelper;

class ContractDate
{
	private \DateTimeImmutable $fecha_inicio;
	private ?\DateTimeImmutable $fecha_fin;

	public function __construct(
		\DateTimeImmutable $fecha_inicio,
		?\DateTimeImmutable $fecha_fin = null
	) {
		$this->validateFechas($fecha_inicio, $fecha_fin);
		$this->fecha_inicio = $fecha_inicio;
		$this->fecha_fin = $fecha_fin;
	}

	private function validateFechas(
		\DateTimeImmutable $fecha_inicio,
		?\DateTimeImmutable $fecha_fin
	): void {
		if ($fecha_fin !== null && $fecha_fin <= $fecha_inicio) {
			throw new \InvalidArgumentException(
				"La fecha de fin debe ser posterior a la fecha de inicio {$fecha_fin->format(
					'Y-m-d H:i:s'
				)}"
			);
		}
	}

	public function getFechaInicio(): \DateTimeImmutable
	{
		return $this->fecha_inicio;
	}

	public function getFechaFin(): ?\DateTimeImmutable
	{
		return $this->fecha_fin;
	}

	public function equals(ContractDate $other): bool
	{
		return $this->fecha_inicio == $other->fecha_inicio && $this->fecha_fin == $other->fecha_fin;
	}
}
