<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\AddServiceRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class TestableAddServiceRequest extends AddServiceRequest
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

class AddServiceRequestTest extends TestCase
{
    use WithFaker;

    private AddServiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
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
        $nombre = $this->faker->words(3, true);
        $descripcion = $this->faker->paragraph();
        $costo = $this->faker->randomFloat(2, 10, 1000);
        $moneda = 'BOB';
        $vigencia = new \DateTimeImmutable('2025-01-01');
        $tipoServicioId = $this->faker->uuid();

        $request = new AddServiceRequest();
        $reflection = new \ReflectionClass($request);

        $nombreProperty = $reflection->getProperty('nombre');
        $nombreProperty->setAccessible(true);
        $nombreProperty->setValue($request, $nombre);

        $descripcionProperty = $reflection->getProperty('descripcion');
        $descripcionProperty->setAccessible(true);
        $descripcionProperty->setValue($request, $descripcion);

        $costoProperty = $reflection->getProperty('costo');
        $costoProperty->setAccessible(true);
        $costoProperty->setValue($request, $costo);

        $monedaProperty = $reflection->getProperty('moneda');
        $monedaProperty->setAccessible(true);
        $monedaProperty->setValue($request, $moneda);

        $vigenciaProperty = $reflection->getProperty('vigencia');
        $vigenciaProperty->setAccessible(true);
        $vigenciaProperty->setValue($request, $vigencia);

        $tipoServicioIdProperty = $reflection->getProperty('tipoServicioId');
        $tipoServicioIdProperty->setAccessible(true);
        $tipoServicioIdProperty->setValue($request, $tipoServicioId);

        $this->assertEquals($nombre, $request->getNombre());
        $this->assertEquals($descripcion, $request->getDescripcion());
        $this->assertEquals($costo, $request->getCosto());
        $this->assertEquals($moneda, $request->getMoneda());
        $this->assertEquals($vigencia, $request->getVigencia());
        $this->assertEquals($tipoServicioId, $request->getTipoServicioId());
    }

    public function testPrepareForValidation(): void
    {
        $nombre = $this->faker->words(3, true);
        $descripcion = $this->faker->paragraph();
        $costo = $this->faker->randomFloat(2, 10, 1000);
        $moneda = 'BOB';
        $vigencia = '2025-01-01';
        $tipoServicioId = $this->faker->uuid();

        $request = new TestableAddServiceRequest();
        $request->setInputData([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'costo' => $costo,
            'moneda' => $moneda,
            'vigencia' => $vigencia,
            'tipo_servicio_id' => $tipoServicioId
        ]);

        $reflection = new \ReflectionClass($request);
        $prepareForValidation = $reflection->getMethod('prepareForValidation');
        $prepareForValidation->setAccessible(true);
        $prepareForValidation->invoke($request);

        $this->assertEquals($nombre, $request->getNombre());
        $this->assertEquals($descripcion, $request->getDescripcion());
        $this->assertEquals($costo, $request->getCosto());
        $this->assertEquals($moneda, $request->getMoneda());
        $this->assertEquals(new \DateTimeImmutable($vigencia), $request->getVigencia());
        $this->assertEquals($tipoServicioId, $request->getTipoServicioId());
    }

    public function testPrepareForValidationWithDefaultMoneda(): void
    {
        $nombre = $this->faker->words(3, true);
        $descripcion = $this->faker->paragraph();
        $costo = $this->faker->randomFloat(2, 10, 1000);
        $vigencia = '2025-01-01';
        $tipoServicioId = $this->faker->uuid();

        $request = new TestableAddServiceRequest();
        $request->setInputData([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'costo' => $costo,
            'vigencia' => $vigencia,
            'tipo_servicio_id' => $tipoServicioId
        ]);

        $reflection = new \ReflectionClass($request);
        $prepareForValidation = $reflection->getMethod('prepareForValidation');
        $prepareForValidation->setAccessible(true);
        $prepareForValidation->invoke($request);

        $this->assertEquals($nombre, $request->getNombre());
        $this->assertEquals($descripcion, $request->getDescripcion());
        $this->assertEquals($costo, $request->getCosto());
        $this->assertEquals('BOB', $request->getMoneda()); // Debe usar el valor por defecto
        $this->assertEquals(new \DateTimeImmutable($vigencia), $request->getVigencia());
        $this->assertEquals($tipoServicioId, $request->getTipoServicioId());
    }
} 