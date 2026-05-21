<?php

namespace App\Http\Requests\Turista;

use App\Http\Requests\Concerns\SanitizesTextInput;
use App\Models\Campana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonacionRequest extends FormRequest
{
    use SanitizesTextInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeFields([
            'visitante_nombre',
            'metodo',
            'referencia_pago',
        ]);
    }

    public function rules(): array
    {
        return [
            'campana_id' => [
                'required',
                Rule::exists('campanas', 'id')->where(
                    fn ($query) => Campana::applyVisibilidadPerfilTurista($query),
                ),
            ],
            'tipo_pago_id' => ['required', 'exists:tipos_pago,id'],
            'visitante_id' => ['nullable', 'exists:visitantes,id'],
            'visitante_nombre' => ['nullable', 'string', 'max:120'],
            'monto' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'metodo' => ['required', 'string', 'max:50'],
            'referencia_pago' => ['nullable', 'string', 'max:150'],
            'payment_uuid' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'campana_id.exists' => __('donacion.campaign_unavailable'),
        ];
    }
}
