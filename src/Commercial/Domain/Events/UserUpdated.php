<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class UserUpdated implements DomainEvent
{
	private string $userId;
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'usuario-actualizado';

	public function __construct(string $userId)
	{
		$this->userId = $userId;
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getUserId(): string
	{
		return $this->userId;
	}

	public function getOccurredOn(): \DateTimeImmutable
	{
		return $this->occurredOn;
	}

	public function getExchangeName(): string
	{
		return self::EXCHANGE_NAME;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->userId,
			'occurredOn' => $this->occurredOn->format('Y-m-d\TH:i:s.u\Z'),
		];
	}
}
