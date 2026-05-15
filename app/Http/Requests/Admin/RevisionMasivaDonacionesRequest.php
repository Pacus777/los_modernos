<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RevisionMasivaDonacionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['integer', 'distinct', 'exists:donaciones,id'],
            'accion' => ['required', 'string', Rule::in(['validar', 'rechazar'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Seleccioná al menos una donación.',
            'ids.max' => 'Podés revisar como máximo 50 donaciones por vez.',
            'ids.*.exists' => 'Una de las donaciones seleccionadas ya no existe.',
            'accion.in' => 'La acción masiva no es válida.',
        ];
    }
}
