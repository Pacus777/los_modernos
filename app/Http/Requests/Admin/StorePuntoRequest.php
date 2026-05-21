<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\SanitizesTextInput;
use Illuminate\Foundation\Http\FormRequest;

class StorePuntoRequest extends FormRequest
{
    use SanitizesTextInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeFields(['nombre', 'descripcion', 'ubicacion']);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'emprendedores' => ['nullable', 'array'],
            'emprendedores.*' => ['integer', 'exists:emprendedores,id'],
        ];
    }
}
