<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreatePatientRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nombre' => ['required', 'string', 'max:100'],
			'apellido' => ['required', 'string', 'max:100'],
			'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
			'fecha_nacimiento' => ['required', 'date'],
			'genero' => ['required', 'string', 'in:M,F,O'],
			'direccion' => ['nullable', 'string', 'max:255'],
			'telefono' => ['nullable', 'string', 'max:20'],
		];
	}

	public function messages(): array
	{
		return [
			'nombre.required' => 'El nombre es requerido',
			'nombre.max' => 'El nombre no puede exceder los 100 caracteres',
			'apellido.required' => 'El apellido es requerido',
			'apellido.max' => 'El apellido no puede exceder los 100 caracteres',
			'email.required' => 'El email es requerido',
			'email.email' => 'El email debe ser una dirección válida',
			'email.unique' => 'Ya existe un usuario registrado con este email',
			'fecha_nacimiento.required' => 'La fecha de nacimiento es requerida',
			'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida',
			'genero.required' => 'El género es requerido',
			'genero.in' => 'El género debe ser M, F u O',
			'direccion.max' => 'La dirección no puede exceder los 255 caracteres',
			'telefono.max' => 'El teléfono no puede exceder los 20 caracteres',
		];
	}

	protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(
			response()->json(
				[
					'success' => false,
					'message' => 'Error de validación',
					'errors' => $validator->errors(),
				],
				422
			)
		);
	}
}
