<?php

declare(strict_types=1);

namespace Commercial\Domain\ValueObjects;

final class Email
{
	private string $value;

	public function __construct(string $email)
	{
		$this->validate($email);
		$this->value = $email;
	}

	private function validate(string $email): void
	{
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new \InvalidArgumentException('Email inválido');
		}
	}

	public function toString(): string
	{
		return $this->value;
	}

	public function equals(self $other): bool
	{
		return $this->value === $other->value;
	}

	public function __toString(): string
	{
		return $this->value;
	}
}
