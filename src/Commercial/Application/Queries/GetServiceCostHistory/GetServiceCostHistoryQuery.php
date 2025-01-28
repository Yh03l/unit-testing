<?php

declare(strict_types=1);

namespace Commercial\Application\Queries\GetServiceCostHistory;

final class GetServiceCostHistoryQuery
{
    public function __construct(
        private readonly string $id
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
}
