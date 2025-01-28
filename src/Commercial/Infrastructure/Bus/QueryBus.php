<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Bus;

interface QueryBus
{
    public function ask(object $query): mixed;
    #public function handle(object $query): mixed;
} 