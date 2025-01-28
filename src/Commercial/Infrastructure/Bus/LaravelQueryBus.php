<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

use Illuminate\Container\Container;

class LaravelQueryBus implements QueryBus
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function ask(object $query): mixed
    {
        $handler = $this->resolveHandler($query);
        return $handler($query);
    }

    private function resolveHandler(object $query): object
    {
        $handlerClass = str_replace('Query', 'Handler', get_class($query));
        return $this->container->make($handlerClass);
    }
}
