<?php
declare(strict_types=1);

namespace Commercial\Application\DTOs;

final class PlanAlimentarioCreadoDTO
{
	private function __construct(
		public readonly string $idPlanAlimentario,
		public readonly string $nombre,
		public readonly string $tipo,
		public readonly int $cantidadDias
	) {}

	public static function fromArray(array $data): self
	{
		return new self(
			$data['IdPlanAlimentario'],
			$data['Nombre'],
			$data['Tipo'],
			$data['CantidadDias']
		);
	}
}
