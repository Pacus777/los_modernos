<?php

namespace App\Http\Requests\Emprendedor;

use App\Enums\RangoMonto;
use App\Models\Donacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltrarDonacionesRequest extends FormRequest
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
            'estado_pago' => ['nullable', 'string', Rule::in([
                Donacion::ESTADO_PENDIENTE,
                Donacion::ESTADO_VALIDADO,
                Donacion::ESTADO_RECHAZADO,
            ])],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'rango_monto' => ['nullable', 'string', Rule::in(RangoMonto::valores())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha_hasta.after_or_equal' => 'La fecha «hasta» debe ser igual o posterior a «desde».',
        ];
    }

    /**
     * @return array{
     *     estado_pago: string,
     *     fecha_desde: string,
     *     fecha_hasta: string,
     *     rango_monto: string
     * }
     */
    public function filtrosNormalizados(): array
    {
        $validated = $this->validated();

        $estado = $validated['estado_pago'] ?? '';
        $estados = [
            Donacion::ESTADO_PENDIENTE,
            Donacion::ESTADO_VALIDADO,
            Donacion::ESTADO_RECHAZADO,
        ];

        return [
            'estado_pago' => in_array($estado, $estados, true) ? $estado : '',
            'fecha_desde' => $validated['fecha_desde'] ?? '',
            'fecha_hasta' => $validated['fecha_hasta'] ?? '',
            'rango_monto' => RangoMonto::desdeFiltro($validated['rango_monto'] ?? '')?->value ?? '',
        ];
    }
}
