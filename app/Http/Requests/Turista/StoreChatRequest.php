<?php

namespace App\Http\Requests\Turista;

use App\Http\Requests\Concerns\SanitizesTextInput;
use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    use SanitizesTextInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeFields(['pregunta']);
    }

    public function rules(): array
    {
        return [
            'pregunta' => ['required', 'string', 'min:2', 'max:300'],
            'idioma' => ['nullable', 'string', 'in:es,en'],
            'context_emprendedor_id' => ['nullable', 'integer', 'exists:emprendedores,id'],
        ];
    }
}
