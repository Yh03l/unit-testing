<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'in:BOB,USD'],
            'vigencia' => ['required', 'date', 'after:today'],
        ];
    }
} 