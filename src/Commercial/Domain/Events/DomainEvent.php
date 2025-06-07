<?php

declare(strict_types=1);

namespace Commercial\Domain\Events;

interface DomainEvent
{
	public function getOccurredOn(): \DateTimeImmutable;
}
