<?php

namespace App\Http\Requests\Emprendedor;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferenciasNotificacionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->emprendedor !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notificar_donaciones_email' => ['required', 'boolean'],
            'notificar_donaciones_panel' => ['required', 'boolean'],
        ];
    }
}
