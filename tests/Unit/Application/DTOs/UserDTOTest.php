<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTOs;

use Commercial\Application\DTOs\UserDTO;
use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\Aggregates\User\Administrator;
use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\ValueObjects\Email;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UserDTOTest extends MockeryTestCase
{
    private string $id;
    private string $nombre;
    private string $apellido;
    private string $email;
    private Email $emailVO;

    protected function setUp(): void
    {
        $this->id = 'user-123';
        $this->nombre = 'Test';
        $this->apellido = 'User';
        $this->email = 'test@example.com';
        $this->emailVO = Email::fromString($this->email);
    }

    public function testFromEntityWithBaseUser(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getId')->andReturn($this->id);
        $user->shouldReceive('getNombre')->andReturn($this->nombre);
        $user->shouldReceive('getApellido')->andReturn($this->apellido);
        $user->shouldReceive('getEmail')->andReturn($this->emailVO);
        $user->shouldReceive('getTipoUsuario')->andReturn('USUARIO');
        $user->shouldReceive('getEstado')->andReturn('activo');

        $dto = UserDTO::fromEntity($user);

        $this->assertEquals($this->id, $dto->id);
        $this->assertEquals($this->nombre, $dto->nombre);
        $this->assertEquals($this->apellido, $dto->apellido);
        $this->assertEquals($this->email, $dto->email);
        $this->assertEquals('USUARIO', $dto->tipo);
        $this->assertEquals('activo', $dto->estado);
        $this->assertNull($dto->cargo);
        $this->assertNull($dto->departamento);
        $this->assertNull($dto->fechaNacimiento);
        $this->assertNull($dto->genero);
        $this->assertNull($dto->direccion);
        $this->assertNull($dto->telefono);
    }

    public function testFromEntityWithAdministrator(): void
    {
        $admin = Mockery::mock(Administrator::class);
        $admin->shouldReceive('getId')->andReturn($this->id);
        $admin->shouldReceive('getNombre')->andReturn($this->nombre);
        $admin->shouldReceive('getApellido')->andReturn($this->apellido);
        $admin->shouldReceive('getEmail')->andReturn($this->emailVO);
        $admin->shouldReceive('getTipoUsuario')->andReturn('ADMINISTRADOR');
        $admin->shouldReceive('getEstado')->andReturn('activo');
        $admin->shouldReceive('getCargo')->andReturn('Gerente');
        $admin->shouldReceive('getDepartamento')->andReturn('TI');

        $dto = UserDTO::fromEntity($admin);

        $this->assertEquals($this->id, $dto->id);
        $this->assertEquals($this->nombre, $dto->nombre);
        $this->assertEquals($this->apellido, $dto->apellido);
        $this->assertEquals($this->email, $dto->email);
        $this->assertEquals('ADMINISTRADOR', $dto->tipo);
        $this->assertEquals('activo', $dto->estado);
        $this->assertEquals('Gerente', $dto->cargo);
        $this->assertEquals('TI', $dto->departamento);
        $this->assertNull($dto->fechaNacimiento);
        $this->assertNull($dto->genero);
        $this->assertNull($dto->direccion);
        $this->assertNull($dto->telefono);
    }

    public function testFromEntityWithPatient(): void
    {
        $fechaNacimiento = new DateTimeImmutable('1990-01-01');
        
        $patient = Mockery::mock(Patient::class);
        $patient->shouldReceive('getId')->andReturn($this->id);
        $patient->shouldReceive('getNombre')->andReturn($this->nombre);
        $patient->shouldReceive('getApellido')->andReturn($this->apellido);
        $patient->shouldReceive('getEmail')->andReturn($this->emailVO);
        $patient->shouldReceive('getTipoUsuario')->andReturn('PACIENTE');
        $patient->shouldReceive('getEstado')->andReturn('activo');
        $patient->shouldReceive('getFechaNacimiento')->andReturn($fechaNacimiento);
        $patient->shouldReceive('getGenero')->andReturn('M');
        $patient->shouldReceive('getDireccion')->andReturn('Calle 123');
        $patient->shouldReceive('getTelefono')->andReturn('123456789');

        $dto = UserDTO::fromEntity($patient);

        $this->assertEquals($this->id, $dto->id);
        $this->assertEquals($this->nombre, $dto->nombre);
        $this->assertEquals($this->apellido, $dto->apellido);
        $this->assertEquals($this->email, $dto->email);
        $this->assertEquals('PACIENTE', $dto->tipo);
        $this->assertEquals('activo', $dto->estado);
        $this->assertNull($dto->cargo);
        $this->assertNull($dto->departamento);
        $this->assertEquals($fechaNacimiento, $dto->fechaNacimiento);
        $this->assertEquals('M', $dto->genero);
        $this->assertEquals('Calle 123', $dto->direccion);
        $this->assertEquals('123456789', $dto->telefono);
    }

    public function testToArrayWithBaseUser(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getId')->andReturn($this->id);
        $user->shouldReceive('getNombre')->andReturn($this->nombre);
        $user->shouldReceive('getApellido')->andReturn($this->apellido);
        $user->shouldReceive('getEmail')->andReturn($this->emailVO);
        $user->shouldReceive('getTipoUsuario')->andReturn('USUARIO');
        $user->shouldReceive('getEstado')->andReturn('activo');

        $dto = UserDTO::fromEntity($user);
        $array = $dto->toArray();

        $expected = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'tipo' => 'USUARIO',
            'estado' => 'activo'
        ];

        $this->assertEquals($expected, $array);
    }

    public function testToArrayWithAdministrator(): void
    {
        $admin = Mockery::mock(Administrator::class);
        $admin->shouldReceive('getId')->andReturn($this->id);
        $admin->shouldReceive('getNombre')->andReturn($this->nombre);
        $admin->shouldReceive('getApellido')->andReturn($this->apellido);
        $admin->shouldReceive('getEmail')->andReturn($this->emailVO);
        $admin->shouldReceive('getTipoUsuario')->andReturn('ADMINISTRADOR');
        $admin->shouldReceive('getEstado')->andReturn('activo');
        $admin->shouldReceive('getCargo')->andReturn('Gerente');
        $admin->shouldReceive('getDepartamento')->andReturn('TI');

        $dto = UserDTO::fromEntity($admin);
        $array = $dto->toArray();

        $expected = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'tipo' => 'ADMINISTRADOR',
            'estado' => 'activo',
            'cargo' => 'Gerente',
            'departamento' => 'TI'
        ];

        $this->assertEquals($expected, $array);
    }

    public function testToArrayWithPatient(): void
    {
        $fechaNacimiento = new DateTimeImmutable('1990-01-01');
        
        $patient = Mockery::mock(Patient::class);
        $patient->shouldReceive('getId')->andReturn($this->id);
        $patient->shouldReceive('getNombre')->andReturn($this->nombre);
        $patient->shouldReceive('getApellido')->andReturn($this->apellido);
        $patient->shouldReceive('getEmail')->andReturn($this->emailVO);
        $patient->shouldReceive('getTipoUsuario')->andReturn('PACIENTE');
        $patient->shouldReceive('getEstado')->andReturn('activo');
        $patient->shouldReceive('getFechaNacimiento')->andReturn($fechaNacimiento);
        $patient->shouldReceive('getGenero')->andReturn('M');
        $patient->shouldReceive('getDireccion')->andReturn('Calle 123');
        $patient->shouldReceive('getTelefono')->andReturn('123456789');

        $dto = UserDTO::fromEntity($patient);
        $array = $dto->toArray();

        $expected = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'tipo' => 'PACIENTE',
            'estado' => 'activo',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'M',
            'direccion' => 'Calle 123',
            'telefono' => '123456789'
        ];

        $this->assertEquals($expected, $array);
    }
}