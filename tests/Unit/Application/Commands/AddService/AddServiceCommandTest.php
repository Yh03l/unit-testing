<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\AddService;

use Commercial\Application\Commands\AddService\AddServiceCommand;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class AddServiceCommandTest extends TestCase
{
	private AddServiceCommand $command;
	private string $nombre;
	private string $descripcion;
	private float $costo;
	private string $moneda;
	private DateTimeImmutable $vigencia;
	private string $tipoServicioId;
	private string $catalogId;

	protected function setUp(): void
	{
		$this->catalogId = 'catalog-456';
		$this->nombre = 'Test Service';
		$this->descripcion = 'Test Description';
		$this->costo = 100.0;
		$this->moneda = 'BOB';
		$this->vigencia = new DateTimeImmutable('2024-12-31');
		$this->tipoServicioId = 'asesoramiento';

		$this->command = new AddServiceCommand(
			$this->catalogId,
			$this->nombre,
			$this->descripcion,
			$this->costo,
			$this->moneda,
			$this->vigencia,
			$this->tipoServicioId
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

	public function testGetCosto(): void
	{
		$this->assertEquals($this->costo, $this->command->getCosto());
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

	public function testGetCatalogId(): void
	{
		$this->assertEquals($this->catalogId, $this->command->getCatalogId());
	}
}
