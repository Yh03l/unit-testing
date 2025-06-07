<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

class CommandResult
{
	private function __construct(
		private readonly bool $success,
		private readonly string $message,
		private readonly ?string $id = null
	) {}

	public static function success(string $id, string $message): self
	{
		return new self(true, $message, $id);
	}

	public static function fail(string $message): self
	{
		return new self(false, $message);
	}

	public function isSuccess(): bool
	{
		return $this->success;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getId(): ?string
	{
		return $this->id;
	}
}
