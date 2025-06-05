<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

interface QueryBus
{
	public function dispatch(object $query): mixed;
}
