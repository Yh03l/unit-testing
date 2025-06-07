<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class ContractCancelled implements DomainEvent
{
	private string $contractId;
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'contrato-cancelado';

	public function __construct(string $contractId)
	{
		$this->contractId = $contractId;
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getContractId(): string
	{
		return $this->contractId;
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
			'idContract' => $this->contractId,
			'occurredOn' => $this->occurredOn->format('Y-m-d\TH:i:s.u\Z'),
		];
	}
}
