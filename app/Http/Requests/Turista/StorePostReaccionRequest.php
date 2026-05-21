<?php

namespace App\Http\Requests\Turista;

use App\Enums\EmprendedorPostReaccionTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostReaccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', Rule::in(EmprendedorPostReaccionTipo::valores())],
        ];
    }
}
