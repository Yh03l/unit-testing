<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class CatalogCreated implements DomainEvent
{
	private string $catalogId;
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'catalogo-creado';

	public function __construct(string $catalogId)
	{
		$this->catalogId = $catalogId;
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getCatalogId(): string
	{
		return $this->catalogId;
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
			'idCatalogo' => $this->catalogId,
			'occurredOn' => $this->occurredOn->format('Y-m-d\TH:i:s.u\Z'),
		];
	}
}
