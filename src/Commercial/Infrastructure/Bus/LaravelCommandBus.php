<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

use Illuminate\Container\Container;

class LaravelCommandBus implements CommandBus
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatch(object $command): void
    {
        $handler = $this->resolveHandler($command);
        $handler($command);
    }

    private function resolveHandler(object $command): object
    {
        $handlerClass = str_replace('Command', 'Handler', get_class($command));
        $handlerClass = str_replace('\\Handlers\\', '\\Commands\\', $handlerClass);
        return $this->container->make($handlerClass);
    }
}