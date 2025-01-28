<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'monto' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'in:BOB,USD'],
            'vigencia' => ['required', 'date'],
            'tipo_servicio_id' => ['required', 'string'],
            'catalogo_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del servicio es requerido',
            'nombre.max' => 'El nombre no puede exceder los 255 caracteres',
            'descripcion.required' => 'La descripción del servicio es requerida',
            'monto.required' => 'El monto es requerido',
            'monto.numeric' => 'El monto debe ser un número',
            'monto.min' => 'El monto debe ser mayor o igual a 0',
            'moneda.required' => 'La moneda es requerida',
            'moneda.in' => 'La moneda debe ser BOB o USD',
            'vigencia.required' => 'La fecha de vigencia es requerida',
            'vigencia.date' => 'La fecha de vigencia debe ser una fecha válida',
            'tipo_servicio_id.required' => 'El tipo de servicio es requerido',
            'catalogo_id.required' => 'El catálogo es requerido',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'monto' => $this->input('monto', 0),
            'vigencia' => $this->input('vigencia', now()->format('Y-m-d H:i:s'))
        ]);
    }
} 