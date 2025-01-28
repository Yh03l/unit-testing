<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetServiceDetails;

final class GetServiceDetailsQuery
{
    public function __construct(
        public readonly string $serviceId
    ) {}
}
