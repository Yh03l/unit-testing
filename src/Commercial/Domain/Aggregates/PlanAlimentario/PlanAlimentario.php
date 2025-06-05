<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\PlanAlimentario;

use Commercial\Domain\ValueObjects\PlanAlimentarioId;

final class PlanAlimentario
{
	private function __construct(
		private PlanAlimentarioId $id,
		private string $nombre,
		private string $tipo,
		private int $cantidadDias
	) {}

	public static function create(
		PlanAlimentarioId $id,
		string $nombre,
		string $tipo,
		int $cantidadDias
	): self {
		return new self($id, $nombre, $tipo, $cantidadDias);
	}

	public function id(): PlanAlimentarioId
	{
		return $this->id;
	}

	public function nombre(): string
	{
		return $this->nombre;
	}

	public function tipo(): string
	{
		return $this->tipo;
	}

	public function cantidadDias(): int
	{
		return $this->cantidadDias;
	}
}
