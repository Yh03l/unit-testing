<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class ServiceRemoved implements DomainEvent
{
	private string $catalogId;
	private string $serviceId;
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'servicio-eliminado';

	public function __construct(string $catalogId, string $serviceId)
	{
		$this->catalogId = $catalogId;
		$this->serviceId = $serviceId;
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getCatalogId(): string
	{
		return $this->catalogId;
	}

	public function getServiceId(): string
	{
		return $this->serviceId;
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
			'idServicio' => $this->serviceId,
			'occurredOn' => $this->occurredOn->format('Y-m-d\TH:i:s.u\Z'),
		];
	}
}
