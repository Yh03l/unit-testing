<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

interface DomainEvent
{
	public function getOccurredOn(): \DateTimeImmutable;

	/**
	 * Retorna el nombre del exchange donde se publicará el evento
	 */
	public function getExchangeName(): string;
}
