<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

use Commercial\Application\Commands\CommandResult;

interface CommandBus
{
	public function dispatch(object $command): CommandResult;
}
