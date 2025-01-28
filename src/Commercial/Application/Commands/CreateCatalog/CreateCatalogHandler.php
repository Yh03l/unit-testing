<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateCatalog;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Aggregates\Catalog\Catalog;
use Commercial\Infrastructure\EventBus\EventBus;
use Illuminate\Support\Str;

class CreateCatalogHandler
{
    public function __construct(
        private readonly CatalogRepository $repository,
        private readonly EventBus $eventBus
    ) {}

    public function __invoke(CreateCatalogCommand $command): void
    {
        $catalog = Catalog::create(
            id: Str::uuid()->toString(),
            nombre: $command->getNombre(),
            estado: $command->getEstado()
        );

        $this->repository->save($catalog);

        // Publicar eventos
        foreach ($catalog->getEvents() as $event) {
            $this->eventBus->publish($event);
        }

        $catalog->clearEvents();
    }
} 