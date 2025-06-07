<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\RemoveService;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Application\Commands\CommandResult;

class RemoveServiceHandler
{
	public function __construct(private readonly CatalogRepository $repository) {}

	public function __invoke(RemoveServiceCommand $command): CommandResult
	{
		$catalog = $this->repository->findById($command->getCatalogId());

		if (!$catalog) {
			throw CatalogException::notFound($command->getCatalogId());
		}

		$catalog->removeService($command->getServiceId());

		$this->repository->save($catalog);

		return CommandResult::success($command->getServiceId(), 'Servicio eliminado exitosamente');
	}

	public function handle(RemoveServiceCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
