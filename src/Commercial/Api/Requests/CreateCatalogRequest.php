<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCatalogRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nombre' => ['required', 'string', 'max:255'],
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
