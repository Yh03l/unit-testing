<?php

declare(strict_types=1);

namespace Commercial\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
} 