<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\RemoveService;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;

class RemoveServiceHandler
{
    private CatalogRepository $repository;

    public function __construct(CatalogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(RemoveServiceCommand $command): void
    {
        $catalog = $this->repository->findById($command->getCatalogId());
        
        if (!$catalog) {
            throw CatalogException::notFound($command->getCatalogId());
        }

        $catalog->removerServicio($command->getServiceId());
        
        $this->repository->save($catalog);
    }
} 