<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Commercial\Domain\Enums\TipoServicio;

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
			'tipo_servicio_id' => [
				'required',
				'string',
				'in:' . implode(',', TipoServicio::values()),
			],
			'catalogo_id' => ['required', 'string', 'exists:catalogos,id'],
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

	protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(
			response()->json(
				[
					'success' => false,
					'message' => 'Validation failed',
					'errors' => $validator->errors(),
				],
				422
			)
		);
	}
}
