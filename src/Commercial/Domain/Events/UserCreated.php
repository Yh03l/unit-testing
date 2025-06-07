<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class UserCreated implements DomainEvent
{
	private string $userId;
	private string $email;
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'cliente-creado';

	public function __construct(string $userId, string $email)
	{
		$this->userId = $userId;
		$this->email = $email;
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getUserId(): string
	{
		return $this->userId;
	}

	public function getEmail(): string
	{
		return $this->email;
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
			'email' => $this->email,
			'occurredOn' => $this->occurredOn->format('Y-m-d\TH:i:s.u\Z'),
		];
	}
}
