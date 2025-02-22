<?php

declare(strict_types=1);

namespace Tests\Unit\Api\Requests;

use Commercial\Api\Requests\CreateUserRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class TestableCreateUserRequest extends CreateUserRequest
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

class CreateUserRequestTest extends TestCase
{
    use WithFaker;

    private CreateUserRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
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

    public function testGetters(): void
    {
        $nombre = $this->faker->firstName();
        $apellido = $this->faker->lastName();
        $email = $this->faker->email();
        $tipoUsuarioId = $this->faker->uuid();

        $request = new CreateUserRequest();
        $reflection = new \ReflectionClass($request);

        $nombreProperty = $reflection->getProperty('nombre');
        $nombreProperty->setAccessible(true);
        $nombreProperty->setValue($request, $nombre);

        $apellidoProperty = $reflection->getProperty('apellido');
        $apellidoProperty->setAccessible(true);
        $apellidoProperty->setValue($request, $apellido);

        $emailProperty = $reflection->getProperty('email');
        $emailProperty->setAccessible(true);
        $emailProperty->setValue($request, $email);

        $tipoUsuarioIdProperty = $reflection->getProperty('tipoUsuarioId');
        $tipoUsuarioIdProperty->setAccessible(true);
        $tipoUsuarioIdProperty->setValue($request, $tipoUsuarioId);

        $this->assertEquals($nombre, $request->getNombre());
        $this->assertEquals($apellido, $request->getApellido());
        $this->assertEquals($email, $request->getEmail());
        $this->assertEquals($tipoUsuarioId, $request->getTipoUsuarioId());
    }

    public function testPrepareForValidation(): void
    {
        $nombre = $this->faker->firstName();
        $apellido = $this->faker->lastName();
        $email = $this->faker->email();
        $tipoUsuarioId = $this->faker->uuid();

        $request = new TestableCreateUserRequest();
        $request->setInputData([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'tipo_usuario_id' => $tipoUsuarioId
        ]);

        $reflection = new \ReflectionClass($request);
        $prepareForValidation = $reflection->getMethod('prepareForValidation');
        $prepareForValidation->setAccessible(true);
        $prepareForValidation->invoke($request);

        $this->assertEquals($nombre, $request->getNombre());
        $this->assertEquals($apellido, $request->getApellido());
        $this->assertEquals($email, $request->getEmail());
        $this->assertEquals($tipoUsuarioId, $request->getTipoUsuarioId());
    }
} 