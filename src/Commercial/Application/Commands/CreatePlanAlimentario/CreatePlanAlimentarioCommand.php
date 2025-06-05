<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreatePlanAlimentario;

final class CreatePlanAlimentarioCommand
{
	public function __construct(
		public readonly string $id,
		public readonly string $nombre,
		public readonly string $tipo,
		public readonly int $cantidadDias
	) {}
}
