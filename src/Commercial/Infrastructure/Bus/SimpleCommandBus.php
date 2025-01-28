<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

use Illuminate\Container\Container;

class SimpleCommandBus implements CommandBus
{
    public function __construct(
        private readonly Container $container
    ) {}

    public function dispatch(object $command): void
    {
        $this->handle($command);
    }

    public function handle(object $command): void
    {
        $handler = $this->container->make($this->getHandlerClass($command));
        $handler->handle($command);
    }

    private function getHandlerClass(object $command): string
    {
        $commandClass = get_class($command);
        return str_replace('Command', 'Handler', $commandClass);
    }
} 