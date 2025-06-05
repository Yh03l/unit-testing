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

	public function dispatch(object $query): mixed
	{
		$handler = $this->container->make($this->getHandlerClass($query));

		// Intentar primero el método handle, si no existe, intentar __invoke
		if (method_exists($handler, 'handle')) {
			return $handler->handle($query);
		}

		if (is_callable($handler)) {
			return $handler($query);
		}

		throw new \RuntimeException(
			sprintf(
				'Handler %s debe implementar el método handle() o ser invocable',
				get_class($handler)
			)
		);
	}

	private function getHandlerClass(object $query): string
	{
		$queryClass = get_class($query);
		$queryNamespace = substr($queryClass, 0, strrpos($queryClass, '\\'));
		$queryName = substr($queryClass, strrpos($queryClass, '\\') + 1);
		$handlerName = str_replace('Query', 'Handler', $queryName);

		return $queryNamespace . '\\' . $handlerName;
	}
}
