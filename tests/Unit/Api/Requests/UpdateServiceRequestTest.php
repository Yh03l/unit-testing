<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\UpdateServiceRequest;
use Tests\TestCase;

class UpdateServiceRequestTest extends TestCase
{
    private UpdateServiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateServiceRequest();
    }

    public function testAuthorize(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function testRules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('nombre', $rules);
        $this->assertArrayHasKey('descripcion', $rules);

        $this->assertEquals(['sometimes', 'required', 'string', 'max:255'], $rules['nombre']);
        $this->assertEquals(['sometimes', 'required', 'string'], $rules['descripcion']);
    }
} 