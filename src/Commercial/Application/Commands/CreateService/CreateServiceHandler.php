<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\CreateService;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceCost;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Application\Commands\CommandResult;
use Illuminate\Support\Str;

final class CreateServiceHandler
{
	public function __construct(
		private readonly ServiceRepository $serviceRepository,
		private readonly CatalogRepository $catalogRepository
	) {}

	public function __invoke(CreateServiceCommand $command): CommandResult
	{
		// Verificar que el catálogo existe y está activo
		$catalog = $this->catalogRepository->findById($command->getCatalogoId());
		if (!$catalog || !$catalog->isActive()) {
			throw CatalogException::catalogNotActive($command->getCatalogoId());
		}

		$serviceId = Str::uuid()->toString();

		$service = new Service(
			id: $serviceId,
			nombre: $command->getNombre(),
			descripcion: $command->getDescripcion(),
			costo: new ServiceCost(
				monto: $command->getMonto(),
				moneda: $command->getMoneda(),
				vigencia: $command->getVigencia()
			),
			tipo_servicio: $command->getTipoServicioId(),
			estado: ServiceStatus::ACTIVO,
			catalogo_id: $command->getCatalogoId()
		);

		$this->serviceRepository->save($service);

		return CommandResult::success($serviceId, 'Servicio creado exitosamente');
	}

	public function handle(CreateServiceCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
