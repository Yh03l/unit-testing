<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Repositories\OutboxRepository;
use Illuminate\Support\Facades\DB;

class EloquentOutboxRepository implements OutboxRepository
{
	private OutboxModel $model;

	public function __construct(OutboxModel $model)
	{
		$this->model = $model;
	}

	public function save(string $eventType, array $eventData): string
	{
		$outbox = $this->model->create([
			'event_type' => $eventType,
			'event_data' => $eventData,
			'status' => 'pending',
			'retry_count' => 0,
		]);

		return $outbox->id;
	}

	public function findPendingEvents(int $limit = 100): array
	{
		return $this->model
			->where('status', 'pending')
			->where('retry_count', '<', 3) // Máximo 3 intentos
			->orderBy('created_at')
			->limit($limit)
			->get()
			->toArray();
	}

	public function markAsPublished(string $id): void
	{
		$this->model->where('id', $id)->update([
			'status' => 'published',
			'published_at' => now(),
		]);
	}

	public function markAsFailed(string $id): void
	{
		$this->model->where('id', $id)->update([
			'status' => 'failed',
		]);
	}

	public function incrementRetryCount(string $id): void
	{
		$this->model->where('id', $id)->increment('retry_count');
	}
}
