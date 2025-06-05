<?php

declare(strict_types=1);

namespace Commercial\Domain\ValueObjects;

final class PlanAlimentarioId
{
	private string $value;

	private function __construct(string $value)
	{
		$this->value = $value;
	}

	public static function fromString(string $value): self
	{
		return new self($value);
	}

	public function value(): string
	{
		return $this->value;
	}
}
