<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

final class CateringContratado implements DomainEvent
{
	private string $idContrato;
	private string $idCliente;
	private string $idPlanAlimentario;
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'catering-contratado';

	public function __construct(string $idContrato, string $idCliente, string $idPlanAlimentario)
	{
		$this->idContrato = $idContrato;
		$this->idCliente = $idCliente;
		$this->idPlanAlimentario = $idPlanAlimentario;
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getContractId(): string
	{
		return $this->idContrato;
	}

	public function getClientId(): string
	{
		return $this->idCliente;
	}

	public function getPlanAlimentarioId(): string
	{
		return $this->idPlanAlimentario;
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
			'idContrato' => $this->idContrato,
			'idCliente' => $this->idCliente,
			'IdPlanAlimentario' => $this->idPlanAlimentario,
			'occurredOn' => $this->occurredOn->format('Y-m-d\TH:i:s.u\Z'),
		];
	}
}
