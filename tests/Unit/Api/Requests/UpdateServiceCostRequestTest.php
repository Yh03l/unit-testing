<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\UpdateServiceCostRequest;
use Tests\TestCase;

class UpdateServiceCostRequestTest extends TestCase
{
    private UpdateServiceCostRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateServiceCostRequest();
    }

    public function testAuthorize(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function testRules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('monto', $rules);
        $this->assertArrayHasKey('moneda', $rules);
        $this->assertArrayHasKey('vigencia', $rules);

        $this->assertEquals(['required', 'numeric', 'min:0'], $rules['monto']);
        $this->assertEquals(['required', 'string', 'in:BOB,USD'], $rules['moneda']);
        $this->assertEquals(['required', 'date', 'after:today'], $rules['vigencia']);
    }
} 