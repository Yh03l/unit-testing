<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\UpdateServiceStatusRequest;
use Tests\TestCase;

class UpdateServiceStatusRequestTest extends TestCase
{
    private UpdateServiceStatusRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateServiceStatusRequest();
    }

    public function testAuthorize(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function testRules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('estado', $rules);
        $this->assertEquals(['required', 'string', 'in:activo,inactivo,suspendido'], $rules['estado']);
    }
} 