<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OutboxModel extends Model
{
	use HasUuids;

	protected $table = 'outbox';
	protected $primaryKey = 'id';
	public $incrementing = false;

	protected $fillable = ['event_type', 'event_data', 'status', 'retry_count', 'published_at'];

	protected $casts = [
		'event_data' => 'array',
		'published_at' => 'datetime',
		'retry_count' => 'integer',
	];
}
