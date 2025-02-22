<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\UpdateCatalogRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class TestableUpdateCatalogRequest extends UpdateCatalogRequest
{
    private array $inputData = [];

    public function setInputData(array $data): void
    {
        $this->inputData = $data;
    }

    public function input($key = null, $default = null)
    {
        if ($key === null) {
            return $this->inputData;
        }

        return $this->inputData[$key] ?? $default;
    }
}

class UpdateCatalogRequestTest extends TestCase
{
    use WithFaker;

    private UpdateCatalogRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateCatalogRequest();
    }

    public function testAuthorize(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function testRules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('nombre', $rules);
        $this->assertArrayHasKey('estado', $rules);

        $this->assertEquals(['sometimes', 'required', 'string', 'max:255'], $rules['nombre']);
        $this->assertEquals(['sometimes', 'required', 'string', 'in:activo,inactivo'], $rules['estado']);
    }

    public function testGetters(): void
    {
        $nombre = $this->faker->words(3, true);
        $estado = 'activo';

        $request = new TestableUpdateCatalogRequest();
        $request->setInputData([
            'nombre' => $nombre,
            'estado' => $estado
        ]);

        $this->assertEquals($nombre, $request->input('nombre'));
        $this->assertEquals($estado, $request->input('estado'));
    }
} 