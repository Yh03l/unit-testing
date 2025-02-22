<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use Commercial\Domain\Exceptions\CatalogException;
use Commercial\Domain\ValueObjects\ServiceStatus;
use PHPUnit\Framework\TestCase;

class CatalogExceptionTest extends TestCase
{
    public function testNotFound(): void
    {
        $id = 'catalog-123';
        $exception = CatalogException::notFound($id);
        $this->assertEquals("Catálogo con ID {$id} no encontrado", $exception->getMessage());
    }

    public function testServiceAlreadyExists(): void
    {
        $serviceId = 'service-123';
        $exception = CatalogException::serviceAlreadyExists($serviceId);
        $this->assertEquals("El servicio con ID {$serviceId} ya existe en el catálogo", $exception->getMessage());
    }

    public function testServiceNotFound(): void
    {
        $serviceId = 'service-123';
        $exception = CatalogException::serviceNotFound($serviceId);
        $this->assertEquals("No se encontró el servicio con ID {$serviceId}", $exception->getMessage());
    }

    public function testInvalidState(): void
    {
        $currentState = 'INVALID';
        $exception = CatalogException::invalidState($currentState);
        $this->assertEquals("Estado inválido del catálogo: {$currentState}", $exception->getMessage());
    }

    public function testInvalidStatusTransition(): void
    {
        $currentStatus = ServiceStatus::ACTIVO;
        $newStatus = ServiceStatus::SUSPENDIDO;
        $exception = CatalogException::invalidStatusTransition($currentStatus, $newStatus);
        $this->assertEquals(
            "No se puede cambiar el estado del servicio de {$currentStatus->toString()} a {$newStatus->toString()}",
            $exception->getMessage()
        );
    }

    public function testServiceCannotBeModified(): void
    {
        $serviceId = 'service-123';
        $exception = CatalogException::serviceCannotBeModified($serviceId);
        $this->assertEquals(
            "El servicio con ID {$serviceId} no puede ser modificado en su estado actual",
            $exception->getMessage()
        );
    }

    public function testServiceCannotUpdateCost(): void
    {
        $serviceId = 'service-123';
        $exception = CatalogException::serviceCannotUpdateCost($serviceId);
        $this->assertEquals(
            "No se puede actualizar el costo del servicio con ID {$serviceId} en su estado actual",
            $exception->getMessage()
        );
    }

    public function testCatalogNotActive(): void
    {
        $catalogId = 'catalog-123';
        $exception = CatalogException::catalogNotActive($catalogId);
        $this->assertEquals("El catálogo con ID {$catalogId} no está activo", $exception->getMessage());
    }

    public function testInvalidCostHistory(): void
    {
        $serviceId = 'service-123';
        $exception = CatalogException::invalidCostHistory($serviceId);
        $this->assertEquals(
            "No se encontró historial de costos para el servicio con ID {$serviceId}",
            $exception->getMessage()
        );
    }
} 