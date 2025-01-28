<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

interface CommandBus
{
    public function dispatch(object $command): void;
    #public function handle(object $command): void;
} 