<?php

declare(strict_types=1);

namespace Commercial\Application\Commands;

class CommandResult
{
	private function __construct(
		private readonly bool $success,
		private readonly ?string $id = null,
		private readonly ?string $message = null
	) {}

	public static function success(?string $id = null, ?string $message = null): self
	{
		return new self(true, $id, $message);
	}

	public static function failure(?string $message = null): self
	{
		return new self(false, null, $message);
	}

	public function isSuccess(): bool
	{
		return $this->success;
	}

	public function getId(): ?string
	{
		return $this->id;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}
}
