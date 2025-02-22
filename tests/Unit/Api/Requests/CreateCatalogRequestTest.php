<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\CreateCatalogRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class CreateCatalogRequestTest extends TestCase
{
    use WithFaker;

    private CreateCatalogRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new CreateCatalogRequest();
    }

    public function testAuthorize(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function testRules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('nombre', $rules);
        $this->assertEquals(['required', 'string', 'max:255'], $rules['nombre']);
    }

    public function testGetters(): void
    {
        $nombre = $this->faker->words(3, true);

        $request = new TestableCreateCatalogRequest();
        $request->setInputData([
            'nombre' => $nombre
        ]);

        $this->assertEquals($nombre, $request->input('nombre'));
    }
}

class TestableCreateCatalogRequest extends CreateCatalogRequest
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