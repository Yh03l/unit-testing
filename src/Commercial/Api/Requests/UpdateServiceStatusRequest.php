<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateServiceStatusRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'estado' => ['required', 'string', 'in:activo,inactivo,suspendido'],
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
