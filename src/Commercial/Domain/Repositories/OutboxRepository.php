<?php

declare(strict_types=1);

namespace Commercial\Domain\Repositories;

interface OutboxRepository
{
	public function save(string $eventType, array $eventData): string;
	public function findPendingEvents(int $limit = 100): array;
	public function markAsPublished(string $id): void;
	public function markAsFailed(string $id): void;
	public function incrementRetryCount(string $id): void;
}
