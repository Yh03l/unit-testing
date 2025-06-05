<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class CreateContractRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'paciente_id' => 'required|uuid|exists:pacientes,id',
			'servicio_id' => 'required|uuid|exists:servicios,id',
			'fecha_inicio' => 'required|date',
			'fecha_fin' => 'nullable|date|after:fecha_inicio',
		];
	}

	protected function prepareForValidation(): void
	{
		// Convertir las fechas a formato ISO 8601 para asegurar una validación consistente
		$request = Request::capture();
		$data = $request->all();

		if (isset($data['fecha_inicio'])) {
			$data['fecha_inicio'] = (new \DateTimeImmutable($data['fecha_inicio']))->format(
				'Y-m-d\TH:i:s.u\Z'
			);
		}

		if (isset($data['fecha_fin'])) {
			$data['fecha_fin'] = (new \DateTimeImmutable($data['fecha_fin']))->format(
				'Y-m-d\TH:i:s.u\Z'
			);
		}

		$request->replace($data);
	}

	protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
}
