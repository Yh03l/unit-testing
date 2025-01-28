<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\AddService;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceCost;
use Illuminate\Support\Str;

class AddServiceHandler
{
    private CatalogRepository $repository;

    public function __construct(CatalogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(AddServiceCommand $command): void
    {
        $catalog = $this->repository->findById($command->getCatalogId());
        
        if (!$catalog) {
            throw CatalogException::notFound($command->getCatalogId());
        }

        $serviceCost = new ServiceCost(
            $command->getCosto(),
            $command->getMoneda(),
            $command->getVigencia()
        );

        $service = new Service(
            (string) Str::uuid(),
            $command->getNombre(),
            $command->getDescripcion(),
            $serviceCost,
            $command->getTipoServicioId(),
            'activo'
        );

        $catalog->agregarServicio($service);
        
        $this->repository->save($catalog);
    }
} 