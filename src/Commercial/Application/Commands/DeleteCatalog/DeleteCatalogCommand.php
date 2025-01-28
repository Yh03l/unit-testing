<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\DeleteCatalog;

final class DeleteCatalogCommand
{
    public function __construct(
        public readonly string $id
    ) {}
} 