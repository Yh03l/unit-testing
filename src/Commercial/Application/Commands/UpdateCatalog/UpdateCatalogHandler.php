<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\UpdateCatalog;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Application\Commands\CommandResult;

final class UpdateCatalogHandler
{
	public function __construct(private readonly CatalogRepository $catalogRepository) {}

	public function __invoke(UpdateCatalogCommand $command): CommandResult
	{
		$catalog = $this->catalogRepository->findById($command->getId());

		if (!$catalog) {
			throw CatalogException::notFound($command->getId());
		}

		$catalog->updateNombre($command->getNombre());

		if ($command->getEstado() !== null) {
			$catalog->updateEstado($command->getEstado());
		}

		$this->catalogRepository->save($catalog);

		return CommandResult::success($command->getId(), 'Catálogo actualizado exitosamente');
	}

	public function handle(UpdateCatalogCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
