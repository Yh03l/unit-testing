<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\CreateServiceRequest;
use Commercial\Domain\Enums\TipoServicio;
use PHPUnit\Framework\TestCase;

class CreateServiceRequestTest extends TestCase
{
	private CreateServiceRequest $request;

	protected function setUp(): void
	{
		$this->request = new CreateServiceRequest();
	}

	public function testRules(): void
	{
		$rules = $this->request->rules();

		$this->assertArrayHasKey('nombre', $rules);
		$this->assertArrayHasKey('descripcion', $rules);
		$this->assertArrayHasKey('monto', $rules);
		$this->assertArrayHasKey('moneda', $rules);
		$this->assertArrayHasKey('vigencia', $rules);
		$this->assertArrayHasKey('tipo_servicio_id', $rules);
		$this->assertArrayHasKey('catalogo_id', $rules);

		$this->assertEquals(['required', 'string', 'max:255'], $rules['nombre']);
		$this->assertEquals(['required', 'string'], $rules['descripcion']);
		$this->assertEquals(['required', 'numeric', 'min:0'], $rules['monto']);
		$this->assertEquals(['required', 'string', 'in:BOB,USD'], $rules['moneda']);
		$this->assertEquals(['required', 'date'], $rules['vigencia']);
		$this->assertEquals(
			['required', 'string', 'in:' . implode(',', TipoServicio::values())],
			$rules['tipo_servicio_id']
		);
		$this->assertEquals(['required', 'string', 'exists:catalogos,id'], $rules['catalogo_id']);
	}

	public function testMessages(): void
	{
		$messages = $this->request->messages();

		$this->assertArrayHasKey('nombre.required', $messages);
		$this->assertArrayHasKey('nombre.max', $messages);
		$this->assertArrayHasKey('descripcion.required', $messages);
		$this->assertArrayHasKey('monto.required', $messages);
		$this->assertArrayHasKey('monto.numeric', $messages);
		$this->assertArrayHasKey('monto.min', $messages);
		$this->assertArrayHasKey('moneda.required', $messages);
		$this->assertArrayHasKey('moneda.in', $messages);
		$this->assertArrayHasKey('vigencia.required', $messages);
		$this->assertArrayHasKey('vigencia.date', $messages);
		$this->assertArrayHasKey('tipo_servicio_id.required', $messages);
		$this->assertArrayHasKey('catalogo_id.required', $messages);

		$this->assertEquals('El nombre del servicio es requerido', $messages['nombre.required']);
		$this->assertEquals(
			'El nombre no puede exceder los 255 caracteres',
			$messages['nombre.max']
		);
		$this->assertEquals(
			'La descripción del servicio es requerida',
			$messages['descripcion.required']
		);
		$this->assertEquals('El monto es requerido', $messages['monto.required']);
		$this->assertEquals('El monto debe ser un número', $messages['monto.numeric']);
		$this->assertEquals('El monto debe ser mayor o igual a 0', $messages['monto.min']);
		$this->assertEquals('La moneda es requerida', $messages['moneda.required']);
		$this->assertEquals('La moneda debe ser BOB o USD', $messages['moneda.in']);
		$this->assertEquals('La fecha de vigencia es requerida', $messages['vigencia.required']);
		$this->assertEquals(
			'La fecha de vigencia debe ser una fecha válida',
			$messages['vigencia.date']
		);
		$this->assertEquals(
			'El tipo de servicio es requerido',
			$messages['tipo_servicio_id.required']
		);
		$this->assertEquals('El catálogo es requerido', $messages['catalogo_id.required']);
	}

	public function testAuthorize(): void
	{
		$this->assertTrue($this->request->authorize());
	}
}
