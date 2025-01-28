<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

use Illuminate\Container\Container;

class SimpleQueryBus implements QueryBus
{
    public function __construct(
        private readonly Container $container
    ) {}

    public function ask(object $query): mixed
    {
        return $this->handle($query);
    }

    public function handle(object $query): mixed
    {
        $handler = $this->container->make($this->getHandlerClass($query));
        return $handler->handle($query);
    }

    private function getHandlerClass(object $query): string
    {
        $queryClass = get_class($query);
        return str_replace('Query', 'Handler', $queryClass);
    }
}
