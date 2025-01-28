<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\RemoveService;

class RemoveServiceCommand
{
    private string $catalogId;
    private string $serviceId;

    public function __construct(string $catalogId, string $serviceId)
    {
        $this->catalogId = $catalogId;
        $this->serviceId = $serviceId;
    }

    public function getCatalogId(): string
    {
        return $this->catalogId;
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }
} 