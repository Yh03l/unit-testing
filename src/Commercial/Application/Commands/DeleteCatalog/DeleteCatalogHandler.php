<?php

declare(strict_types=1);

namespace Commercial\Application\Commands\DeleteCatalog;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Application\Commands\CommandResult;

final class DeleteCatalogHandler
{
	public function __construct(private readonly CatalogRepository $catalogRepository) {}

	public function __invoke(DeleteCatalogCommand $command): CommandResult
	{
		$catalog = $this->catalogRepository->findById($command->id);

		if (!$catalog) {
			throw CatalogException::notFound($command->id);
		}

		$this->catalogRepository->delete($command->id);

		return CommandResult::success($command->id, 'Catálogo eliminado exitosamente');
	}

	public function handle(DeleteCatalogCommand $command): CommandResult
	{
		return $this->__invoke($command);
	}
}
