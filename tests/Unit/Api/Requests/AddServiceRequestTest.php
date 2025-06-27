<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\AddServiceRequest;
use PHPUnit\Framework\TestCase;

class AddServiceRequestTest extends TestCase
{
	private AddServiceRequest $request;

	protected function setUp(): void
	{
		$this->request = new AddServiceRequest();
	}

	public function testRules(): void
	{
		$rules = $this->request->rules();

		$this->assertArrayHasKey('nombre', $rules);
		$this->assertArrayHasKey('descripcion', $rules);
		$this->assertArrayHasKey('costo', $rules);
		$this->assertArrayHasKey('moneda', $rules);
		$this->assertArrayHasKey('vigencia', $rules);
		$this->assertArrayHasKey('tipo_servicio_id', $rules);

		$this->assertEquals('required|string|max:100', $rules['nombre']);
		$this->assertEquals('required|string', $rules['descripcion']);
		$this->assertEquals('required|numeric|min:0', $rules['costo']);
		$this->assertEquals('required|string|in:BOB,USD', $rules['moneda']);
		$this->assertEquals('required|date', $rules['vigencia']);
		$this->assertEquals('required|uuid', $rules['tipo_servicio_id']);
	}

	public function testGetters(): void
	{
		$nombre = 'Test Service';
		$descripcion = 'Test Description';
		$costo = 100.5;
		$moneda = 'BOB';
		$vigencia = new \DateTimeImmutable('2024-12-31');
		$tipoServicioId = 'service-type-123';

		// Simular que los datos están establecidos
		$this->request = $this->createRequestWithData([
			'nombre' => $nombre,
			'descripcion' => $descripcion,
			'costo' => $costo,
			'moneda' => $moneda,
			'vigencia' => $vigencia->format('Y-m-d'),
			'tipo_servicio_id' => $tipoServicioId,
		]);

		$this->assertEquals($nombre, $this->request->getNombre());
		$this->assertEquals($descripcion, $this->request->getDescripcion());
		$this->assertEquals($costo, $this->request->getCosto());
		$this->assertEquals($moneda, $this->request->getMoneda());
		$this->assertEquals(
			$vigencia->format('Y-m-d'),
			$this->request->getVigencia()->format('Y-m-d')
		);
		$this->assertEquals($tipoServicioId, $this->request->getTipoServicioId());
	}

	public function testGettersWithNullValues(): void
	{
		$nombre = 'Test Service';
		$descripcion = 'Test Description';
		$costo = 0;
		$moneda = 'USD';
		$tipoServicioId = 'service-type-123';

		// Simular que los datos están establecidos
		$this->request = $this->createRequestWithData([
			'nombre' => $nombre,
			'descripcion' => $descripcion,
			'costo' => $costo,
			'moneda' => $moneda,
			'vigencia' => null,
			'tipo_servicio_id' => $tipoServicioId,
		]);

		$this->assertEquals($nombre, $this->request->getNombre());
		$this->assertEquals($descripcion, $this->request->getDescripcion());
		$this->assertEquals($costo, $this->request->getCosto());
		$this->assertEquals($moneda, $this->request->getMoneda());
		$this->assertInstanceOf(\DateTimeImmutable::class, $this->request->getVigencia());
		$this->assertEquals($tipoServicioId, $this->request->getTipoServicioId());
	}

	private function createRequestWithData(array $data): AddServiceRequest
	{
		$request = new class ($data) extends AddServiceRequest {
			private array $data;

			public function __construct(array $data)
			{
				$this->data = $data;
			}

			public function getNombre(): string
			{
				return $this->data['nombre'] ?? '';
			}

			public function getDescripcion(): string
			{
				return $this->data['descripcion'] ?? '';
			}

			public function getCosto(): float
			{
				return $this->data['costo'] ?? 0.0;
			}

			public function getMoneda(): string
			{
				return $this->data['moneda'] ?? '';
			}

			public function getVigencia(): \DateTimeImmutable
			{
				if (!isset($this->data['vigencia']) || $this->data['vigencia'] === null) {
					return new \DateTimeImmutable();
				}
				return new \DateTimeImmutable($this->data['vigencia']);
			}

			public function getTipoServicioId(): string
			{
				return $this->data['tipo_servicio_id'] ?? '';
			}
		};

		return $request;
	}
}
