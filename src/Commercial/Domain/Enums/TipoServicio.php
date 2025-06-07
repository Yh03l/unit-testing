<?php

declare(strict_types=1);

namespace Commercial\Domain\Enums;

enum TipoServicio: string
{
	case ASESORAMIENTO = 'asesoramiento';
	case CATERING = 'catering';

	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	public static function fromString(string $value): self
	{
		return match (strtolower($value)) {
			'asesoramiento' => self::ASESORAMIENTO,
			'catering' => self::CATERING,
			default => throw new \InvalidArgumentException("Tipo de servicio inválido: {$value}"),
		};
	}

	public function toString(): string
	{
		return $this->value;
	}
}
