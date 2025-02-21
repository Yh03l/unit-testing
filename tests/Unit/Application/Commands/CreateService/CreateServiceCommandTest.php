<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CreateService;

use Commercial\Application\Commands\CreateService\CreateServiceCommand;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class CreateServiceCommandTest extends TestCase
{
    private CreateServiceCommand $command;
    private string $nombre;
    private string $descripcion;
    private float $monto;
    private string $moneda;
    private DateTimeImmutable $vigencia;
    private string $tipoServicioId;
    private string $catalogoId;

    protected function setUp(): void
    {
        $this->nombre = 'Test Service';
        $this->descripcion = 'Test Description';
        $this->monto = 100.00;
        $this->moneda = 'BOB';
        $this->vigencia = new DateTimeImmutable('2024-12-31');
        $this->tipoServicioId = 'tipo-123';
        $this->catalogoId = 'catalog-456';

        $this->command = new CreateServiceCommand(
            $this->nombre,
            $this->descripcion,
            $this->monto,
            $this->moneda,
            $this->vigencia,
            $this->tipoServicioId,
            $this->catalogoId
        );
    }

    public function testGetNombre(): void
    {
        $this->assertEquals($this->nombre, $this->command->getNombre());
    }

    public function testGetDescripcion(): void
    {
        $this->assertEquals($this->descripcion, $this->command->getDescripcion());
    }

    public function testGetMonto(): void
    {
        $this->assertEquals($this->monto, $this->command->getMonto());
    }

    public function testGetMoneda(): void
    {
        $this->assertEquals($this->moneda, $this->command->getMoneda());
    }

    public function testGetVigencia(): void
    {
        $this->assertEquals($this->vigencia, $this->command->getVigencia());
    }

    public function testGetTipoServicioId(): void
    {
        $this->assertEquals($this->tipoServicioId, $this->command->getTipoServicioId());
    }

    public function testGetCatalogoId(): void
    {
        $this->assertEquals($this->catalogoId, $this->command->getCatalogoId());
    }
} 