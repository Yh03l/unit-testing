<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Http\Request;

class CreateUserRequest extends Request
{
    private string $nombre;
    private string $apellido;
    private string $email;
    private string $tipoUsuarioId;

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'tipo_usuario_id' => 'required|uuid'
        ];
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTipoUsuarioId(): string
    {
        return $this->tipoUsuarioId;
    }

    protected function prepareForValidation(): void
    {
        $this->nombre = $this->input('nombre');
        $this->apellido = $this->input('apellido');
        $this->email = $this->input('email');
        $this->tipoUsuarioId = $this->input('tipo_usuario_id');
    }
} 