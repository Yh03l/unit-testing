<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\CreateUserRequest;
use PHPUnit\Framework\TestCase;

class CreateUserRequestTest extends TestCase
{
	private CreateUserRequest $request;

	protected function setUp(): void
	{
		$this->request = new CreateUserRequest();
	}

	public function testRules(): void
	{
		$rules = $this->request->rules();

		$this->assertArrayHasKey('nombre', $rules);
		$this->assertArrayHasKey('apellido', $rules);
		$this->assertArrayHasKey('email', $rules);
		$this->assertArrayHasKey('tipo_usuario_id', $rules);

		$this->assertEquals('required|string|max:100', $rules['nombre']);
		$this->assertEquals('required|string|max:100', $rules['apellido']);
		$this->assertEquals('required|email|max:255', $rules['email']);
		$this->assertEquals('required|uuid', $rules['tipo_usuario_id']);
	}

	public function testAuthorize(): void
	{
		$this->assertTrue($this->request->authorize());
	}
}
