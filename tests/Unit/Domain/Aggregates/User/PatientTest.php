<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregates\User;

use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class PatientTest extends TestCase
{
    private string $id;
    private string $nombre;
    private string $apellido;
    private Email $email;
    private string $estado;
    private DateTimeImmutable $fechaNacimiento;
    private string $genero;
    private string $direccion;
    private string $telefono;
    private Patient $patient;

    protected function setUp(): void
    {
        $this->id = 'test-id';
        $this->nombre = 'John';
        $this->apellido = 'Doe';
        $this->email = Email::fromString('john.doe@example.com');
        $this->estado = 'ACTIVO';
        $this->fechaNacimiento = new DateTimeImmutable('1990-01-01');
        $this->genero = 'M';
        $this->direccion = 'Calle Principal 123';
        $this->telefono = '1234567890';
        
        $this->patient = new Patient(
            $this->id,
            $this->nombre,
            $this->apellido,
            $this->email,
            $this->estado,
            $this->fechaNacimiento,
            $this->genero,
            $this->direccion,
            $this->telefono
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals($this->id, $this->patient->getId());
    }

    public function testGetNombre(): void
    {
        $this->assertEquals($this->nombre, $this->patient->getNombre());
    }

    public function testGetApellido(): void
    {
        $this->assertEquals($this->apellido, $this->patient->getApellido());
    }

    public function testGetEmail(): void
    {
        $this->assertEquals($this->email, $this->patient->getEmail());
    }

    public function testGetEstado(): void
    {
        $this->assertEquals($this->estado, $this->patient->getEstado());
    }

    public function testGetFechaNacimiento(): void
    {
        $this->assertEquals($this->fechaNacimiento, $this->patient->getFechaNacimiento());
    }

    public function testGetGenero(): void
    {
        $this->assertEquals($this->genero, $this->patient->getGenero());
    }

    public function testGetDireccion(): void
    {
        $this->assertEquals($this->direccion, $this->patient->getDireccion());
    }

    public function testGetTelefono(): void
    {
        $this->assertEquals($this->telefono, $this->patient->getTelefono());
    }

    public function testUpdateInformation(): void
    {
        $newNombre = 'Jane';
        $newApellido = 'Smith';
        
        $this->patient->updateInformation($newNombre, $newApellido);
        
        $this->assertEquals($newNombre, $this->patient->getNombre());
        $this->assertEquals($newApellido, $this->patient->getApellido());
    }

    public function testUpdateEmail(): void
    {
        $newEmail = Email::fromString('jane.smith@example.com');
        
        $this->patient->updateEmail($newEmail);
        
        $this->assertEquals($newEmail, $this->patient->getEmail());
    }

    public function testUpdateEstado(): void
    {
        $newEstado = 'INACTIVO';
        
        $this->patient->updateEstado($newEstado);
        
        $this->assertEquals($newEstado, $this->patient->getEstado());
    }

    public function testUpdateContactInfo(): void
    {
        $newDireccion = 'Avenida Central 456';
        $newTelefono = '0987654321';
        
        $this->patient->updateContactInfo($newDireccion, $newTelefono);
        
        $this->assertEquals($newDireccion, $this->patient->getDireccion());
        $this->assertEquals($newTelefono, $this->patient->getTelefono());
    }

    public function testGetTipoUsuario(): void
    {
        $this->assertEquals('PACIENTE', $this->patient->getTipoUsuario());
    }
} 