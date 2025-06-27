<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

use Commercial\Application\Commands\CommandResult;
use Illuminate\Container\Container;

class SimpleCommandBus implements CommandBus
{
	public function __construct(private readonly Container $container) {}

	public function dispatch(object $command): CommandResult
	{
		$this->handle($command);
		return CommandResult::success(null, 'Command executed successfully');
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
