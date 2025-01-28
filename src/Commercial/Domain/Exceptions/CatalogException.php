<?php

declare(strict_types=1);

namespace Commercial\Domain\Exceptions;

use Commercial\Domain\ValueObjects\ServiceStatus;

class CatalogException extends \DomainException
{
    public static function notFound(string $id): self
    {
        return new self("Catálogo con ID {$id} no encontrado");
    }

    public static function serviceAlreadyExists(string $serviceId): self
    {
        return new self("El servicio con ID {$serviceId} ya existe en el catálogo");
    }

    public static function serviceNotFound(string $serviceId): self
    {
        return new self("No se encontró el servicio con ID {$serviceId}");
    }

    public static function invalidState(string $currentState): self
    {
        return new self("Estado inválido del catálogo: {$currentState}");
    }

    public static function invalidStatusTransition(ServiceStatus $currentStatus, ServiceStatus $newStatus): self
    {
        return new self(
            "No se puede cambiar el estado del servicio de {$currentStatus->toString()} a {$newStatus->toString()}"
        );
    }

    public static function serviceCannotBeModified(string $serviceId): self
    {
        return new self("El servicio con ID {$serviceId} no puede ser modificado en su estado actual");
    }

    public static function serviceCannotUpdateCost(string $serviceId): self
    {
        return new self("No se puede actualizar el costo del servicio con ID {$serviceId} en su estado actual");
    }

    public static function catalogNotActive(string $catalogId): self
    {
        return new self("El catálogo con ID {$catalogId} no está activo");
    }

    public static function invalidCostHistory(string $serviceId): self
    {
        return new self("No se encontró historial de costos para el servicio con ID {$serviceId}");
    }
} 