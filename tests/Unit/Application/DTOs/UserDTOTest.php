<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTOs;

use Commercial\Application\DTOs\UserDTO;
use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\Aggregates\User\Administrator;
use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\ValueObjects\Email;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class UserDTOTest extends TestCase
{
    private string $id;
    private string $nombre;
    private string $apellido;
    private Email $email;
    private string $estado;

    protected function setUp(): void
    {
        $this->id = 'user-123';
        $this->nombre = 'John';
        $this->apellido = 'Doe';
        $this->email = Email::fromString('john.doe@example.com');
        $this->estado = 'ACTIVO';
    }

    public function testFromEntityWithBaseUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($this->id);
        $user->method('getNombre')->willReturn($this->nombre);
        $user->method('getApellido')->willReturn($this->apellido);
        $user->method('getEmail')->willReturn($this->email);
        $user->method('getTipoUsuario')->willReturn('USER');
        $user->method('getEstado')->willReturn($this->estado);

        $dto = UserDTO::fromEntity($user);

        $this->assertEquals($this->id, $dto->id);
        $this->assertEquals($this->nombre, $dto->nombre);
        $this->assertEquals($this->apellido, $dto->apellido);
        $this->assertEquals($this->email->getValue(), $dto->email);
        $this->assertEquals('USER', $dto->tipo);
        $this->assertEquals($this->estado, $dto->estado);
        $this->assertNull($dto->cargo);
        $this->assertNull($dto->departamento);
        $this->assertNull($dto->fechaNacimiento);
        $this->assertNull($dto->genero);
        $this->assertNull($dto->direccion);
        $this->assertNull($dto->telefono);
    }

    public function testFromEntityWithAdministrator(): void
    {
        $cargo = 'Director';
        $departamento = 'Tecnología';

        $admin = $this->createMock(Administrator::class);
        $admin->method('getId')->willReturn($this->id);
        $admin->method('getNombre')->willReturn($this->nombre);
        $admin->method('getApellido')->willReturn($this->apellido);
        $admin->method('getEmail')->willReturn($this->email);
        $admin->method('getTipoUsuario')->willReturn('ADMINISTRADOR');
        $admin->method('getEstado')->willReturn($this->estado);
        $admin->method('getCargo')->willReturn($cargo);
        $admin->method('getDepartamento')->willReturn($departamento);

        $dto = UserDTO::fromEntity($admin);

        $this->assertEquals($this->id, $dto->id);
        $this->assertEquals($this->nombre, $dto->nombre);
        $this->assertEquals($this->apellido, $dto->apellido);
        $this->assertEquals($this->email->getValue(), $dto->email);
        $this->assertEquals('ADMINISTRADOR', $dto->tipo);
        $this->assertEquals($this->estado, $dto->estado);
        $this->assertEquals($cargo, $dto->cargo);
        $this->assertEquals($departamento, $dto->departamento);
    }

    public function testFromEntityWithPatient(): void
    {
        $fechaNacimiento = new DateTimeImmutable('1990-01-01');
        $genero = 'M';
        $direccion = 'Calle Principal 123';
        $telefono = '1234567890';

        $patient = $this->createMock(Patient::class);
        $patient->method('getId')->willReturn($this->id);
        $patient->method('getNombre')->willReturn($this->nombre);
        $patient->method('getApellido')->willReturn($this->apellido);
        $patient->method('getEmail')->willReturn($this->email);
        $patient->method('getTipoUsuario')->willReturn('PACIENTE');
        $patient->method('getEstado')->willReturn($this->estado);
        $patient->method('getFechaNacimiento')->willReturn($fechaNacimiento);
        $patient->method('getGenero')->willReturn($genero);
        $patient->method('getDireccion')->willReturn($direccion);
        $patient->method('getTelefono')->willReturn($telefono);

        $dto = UserDTO::fromEntity($patient);

        $this->assertEquals($this->id, $dto->id);
        $this->assertEquals($this->nombre, $dto->nombre);
        $this->assertEquals($this->apellido, $dto->apellido);
        $this->assertEquals($this->email->getValue(), $dto->email);
        $this->assertEquals('PACIENTE', $dto->tipo);
        $this->assertEquals($this->estado, $dto->estado);
        $this->assertEquals($fechaNacimiento, $dto->fechaNacimiento);
        $this->assertEquals($genero, $dto->genero);
        $this->assertEquals($direccion, $dto->direccion);
        $this->assertEquals($telefono, $dto->telefono);
    }

    public function testToArrayWithBaseUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($this->id);
        $user->method('getNombre')->willReturn($this->nombre);
        $user->method('getApellido')->willReturn($this->apellido);
        $user->method('getEmail')->willReturn($this->email);
        $user->method('getTipoUsuario')->willReturn('USER');
        $user->method('getEstado')->willReturn($this->estado);

        $dto = UserDTO::fromEntity($user);
        $array = $dto->toArray();

        $expectedArray = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email->getValue(),
            'tipo' => 'USER',
            'estado' => $this->estado
        ];

        $this->assertEquals($expectedArray, $array);
    }

    public function testToArrayWithAdministrator(): void
    {
        $cargo = 'Director';
        $departamento = 'Tecnología';

        $admin = $this->createMock(Administrator::class);
        $admin->method('getId')->willReturn($this->id);
        $admin->method('getNombre')->willReturn($this->nombre);
        $admin->method('getApellido')->willReturn($this->apellido);
        $admin->method('getEmail')->willReturn($this->email);
        $admin->method('getTipoUsuario')->willReturn('ADMINISTRADOR');
        $admin->method('getEstado')->willReturn($this->estado);
        $admin->method('getCargo')->willReturn($cargo);
        $admin->method('getDepartamento')->willReturn($departamento);

        $dto = UserDTO::fromEntity($admin);
        $array = $dto->toArray();

        $expectedArray = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email->getValue(),
            'tipo' => 'ADMINISTRADOR',
            'estado' => $this->estado,
            'cargo' => $cargo,
            'departamento' => $departamento
        ];

        $this->assertEquals($expectedArray, $array);
    }

    public function testToArrayWithPatient(): void
    {
        $fechaNacimiento = new DateTimeImmutable('1990-01-01');
        $genero = 'M';
        $direccion = 'Calle Principal 123';
        $telefono = '1234567890';

        $patient = $this->createMock(Patient::class);
        $patient->method('getId')->willReturn($this->id);
        $patient->method('getNombre')->willReturn($this->nombre);
        $patient->method('getApellido')->willReturn($this->apellido);
        $patient->method('getEmail')->willReturn($this->email);
        $patient->method('getTipoUsuario')->willReturn('PACIENTE');
        $patient->method('getEstado')->willReturn($this->estado);
        $patient->method('getFechaNacimiento')->willReturn($fechaNacimiento);
        $patient->method('getGenero')->willReturn($genero);
        $patient->method('getDireccion')->willReturn($direccion);
        $patient->method('getTelefono')->willReturn($telefono);

        $dto = UserDTO::fromEntity($patient);
        $array = $dto->toArray();

        $expectedArray = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email->getValue(),
            'tipo' => 'PACIENTE',
            'estado' => $this->estado,
            'fecha_nacimiento' => '1990-01-01',
            'genero' => $genero,
            'direccion' => $direccion,
            'telefono' => $telefono
        ];

        $this->assertEquals($expectedArray, $array);
    }
}