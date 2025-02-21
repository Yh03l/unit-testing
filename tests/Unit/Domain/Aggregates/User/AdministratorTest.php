<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregates\User;

use Commercial\Domain\Aggregates\User\Administrator;
use Commercial\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class AdministratorTest extends TestCase
{
    private string $id;
    private string $nombre;
    private string $apellido;
    private Email $email;
    private string $estado;
    private string $cargo;
    private string $departamento;
    private Administrator $administrator;

    protected function setUp(): void
    {
        $this->id = 'test-id';
        $this->nombre = 'John';
        $this->apellido = 'Doe';
        $this->email = Email::fromString('john.doe@example.com');
        $this->estado = 'ACTIVO';
        $this->cargo = 'Director';
        $this->departamento = 'Tecnología';
        
        $this->administrator = new Administrator(
            $this->id,
            $this->nombre,
            $this->apellido,
            $this->email,
            $this->estado,
            $this->cargo,
            $this->departamento
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals($this->id, $this->administrator->getId());
    }

    public function testGetNombre(): void
    {
        $this->assertEquals($this->nombre, $this->administrator->getNombre());
    }

    public function testGetApellido(): void
    {
        $this->assertEquals($this->apellido, $this->administrator->getApellido());
    }

    public function testGetEmail(): void
    {
        $this->assertEquals($this->email, $this->administrator->getEmail());
    }

    public function testGetEstado(): void
    {
        $this->assertEquals($this->estado, $this->administrator->getEstado());
    }

    public function testGetCargo(): void
    {
        $this->assertEquals($this->cargo, $this->administrator->getCargo());
    }

    public function testGetDepartamento(): void
    {
        $this->assertEquals($this->departamento, $this->administrator->getDepartamento());
    }

    public function testUpdateInformation(): void
    {
        $newNombre = 'Jane';
        $newApellido = 'Smith';
        
        $this->administrator->updateInformation($newNombre, $newApellido);
        
        $this->assertEquals($newNombre, $this->administrator->getNombre());
        $this->assertEquals($newApellido, $this->administrator->getApellido());
    }

    public function testUpdateEmail(): void
    {
        $newEmail = Email::fromString('jane.smith@example.com');
        
        $this->administrator->updateEmail($newEmail);
        
        $this->assertEquals($newEmail, $this->administrator->getEmail());
    }

    public function testUpdateEstado(): void
    {
        $newEstado = 'INACTIVO';
        
        $this->administrator->updateEstado($newEstado);
        
        $this->assertEquals($newEstado, $this->administrator->getEstado());
    }

    public function testUpdateCargo(): void
    {
        $newCargo = 'Gerente';
        
        $this->administrator->updateCargo($newCargo);
        
        $this->assertEquals($newCargo, $this->administrator->getCargo());
    }

    public function testUpdateDepartamento(): void
    {
        $newDepartamento = 'Recursos Humanos';
        
        $this->administrator->updateDepartamento($newDepartamento);
        
        $this->assertEquals($newDepartamento, $this->administrator->getDepartamento());
    }

    public function testGetTipoUsuario(): void
    {
        $this->assertEquals('ADMINISTRADOR', $this->administrator->getTipoUsuario());
    }
} 