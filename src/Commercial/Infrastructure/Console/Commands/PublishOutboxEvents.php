<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Console\Commands;

use Commercial\Infrastructure\EventBus\EventBus;
use Illuminate\Console\Command;

class PublishOutboxEvents extends Command
{
	protected $signature = 'commercial:publish-events';
	protected $description = 'Publica los eventos pendientes en el outbox';

	private EventBus $eventBus;

	public function __construct(EventBus $eventBus)
	{
		parent::__construct();
		$this->eventBus = $eventBus;
	}

	public function handle(): int
	{
		$this->info('Iniciando publicación de eventos pendientes...');

		try {
			$this->eventBus->publishPendingEvents();
			$this->info('Eventos publicados exitosamente');
			return self::SUCCESS;
		} catch (\Exception $e) {
			$this->error('Error al publicar eventos: ' . $e->getMessage());
			return self::FAILURE;
		}
	}
}
