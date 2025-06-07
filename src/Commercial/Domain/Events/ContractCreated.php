<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

class ContractCreated implements DomainEvent
{
	private string $contractId;
	private string $pacienteId;
	private string $servicioId;
	private \DateTimeImmutable $occurredOn;
	private const EXCHANGE_NAME = 'evaluacion-nutricional-contratado';

	public function __construct(string $contractId, string $pacienteId, string $servicioId)
	{
		$this->contractId = $contractId;
		$this->pacienteId = $pacienteId;
		$this->servicioId = $servicioId;
		$this->occurredOn = new \DateTimeImmutable();
	}

	public function getContractId(): string
	{
		return $this->contractId;
	}

	public function getPacienteId(): string
	{
		return $this->pacienteId;
	}

	public function getServicioId(): string
	{
		return $this->servicioId;
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
			'idContrato' => $this->contractId,
			'idCliente' => $this->pacienteId,
			'idServicio' => $this->servicioId,
			'occurredOn' => $this->occurredOn->format('Y-m-d\TH:i:s.u\Z'),
		];
	}
}
