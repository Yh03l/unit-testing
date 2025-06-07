<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class AddServiceRequest extends Request
{
	private string $nombre;
	private string $descripcion;
	private float $costo;
	private string $moneda;
	private \DateTimeImmutable $vigencia;
	private string $tipoServicioId;

	public function getNombre(): string
	{
		return $this->nombre;
	}

	public function getDescripcion(): string
	{
		return $this->descripcion;
	}

	public function getCosto(): float
	{
		return $this->costo;
	}

	public function getMoneda(): string
	{
		return $this->moneda;
	}

	public function getVigencia(): \DateTimeImmutable
	{
		return $this->vigencia;
	}

	public function getTipoServicioId(): string
	{
		return $this->tipoServicioId;
	}

	public function rules(): array
	{
		return [
			'nombre' => 'required|string|max:100',
			'descripcion' => 'required|string',
			'costo' => 'required|numeric|min:0',
			'moneda' => 'required|string|in:BOB,USD',
			'vigencia' => 'required|date',
			'tipo_servicio_id' => 'required|uuid',
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
