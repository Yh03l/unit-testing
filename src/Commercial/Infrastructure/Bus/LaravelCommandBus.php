<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

use Commercial\Application\Commands\CommandResult;
use Illuminate\Container\Container;

class LaravelCommandBus implements CommandBus
{
	private Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function dispatch(object $command): CommandResult
	{
		$handler = $this->container->make($this->getHandlerClass($command));

		// Intentar primero el método handle, si no existe, intentar __invoke
		if (method_exists($handler, 'handle')) {
			return $handler->handle($command);
		}

		if (is_callable($handler)) {
			return $handler($command);
		}

		throw new \RuntimeException(
			sprintf(
				'Handler %s debe implementar el método handle() o ser invocable',
				get_class($handler)
			)
		);
	}

	private function getHandlerClass(object $command): string
	{
		$commandClass = get_class($command);
		$lastBackslashPos = strrpos($commandClass, '\\');

		if ($lastBackslashPos === false) {
			// Si no hay namespace, solo usar el nombre de la clase
			$commandName = $commandClass;
			$handlerName = str_replace('Command', 'Handler', $commandName);
			return $handlerName;
		}

		$commandNamespace = substr($commandClass, 0, $lastBackslashPos);
		$commandName = substr($commandClass, $lastBackslashPos + 1);
		$handlerName = str_replace('Command', 'Handler', $commandName);

		return $commandNamespace . '\\' . $handlerName;
	}
}
