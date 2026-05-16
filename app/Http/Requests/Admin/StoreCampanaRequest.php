<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidaFechasCampana;
use App\Models\Campana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampanaRequest extends FormRequest
{
    use ValidaFechasCampana;

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'fecha_inicio' => $this->filled('fecha_inicio') ? $this->input('fecha_inicio') : null,
            'fecha_fin' => $this->filled('fecha_fin') ? $this->input('fecha_fin') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'emprendedor_id' => ['required', 'exists:emprendedores,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'meta_apoyo' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'fecha_inicio' => ['required', 'date', 'after_or_equal:today'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => [
                'required',
                Rule::in([
                    Campana::ESTADO_ACTIVA,
                    Campana::ESTADO_INACTIVA,
                    Campana::ESTADO_FINALIZADA,
                ]),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validarRangoFechasCampana($validator);
            $this->validarCampanaActivaVigente($validator);

            if ($this->input('estado') !== Campana::ESTADO_ACTIVA) {
                return;
            }

            $existe = Campana::query()
                ->where('emprendedor_id', $this->input('emprendedor_id'))
                ->where('estado', Campana::ESTADO_ACTIVA)
                ->exists();

            if ($existe) {
                $validator->errors()->add(
                    'estado',
                    'Este emprendedor ya tiene una campaña activa. Finalizá o desactivá la otra antes de crear o activar otra.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'emprendedor_id.required' => 'Debés elegir un emprendedor.',
            'titulo.required' => 'El título de la campaña es obligatorio.',
            'meta_apoyo.required' => 'La meta de apoyo es obligatoria.',
            'meta_apoyo.min' => 'La meta debe ser mayor a cero.',
            'estado.in' => 'El estado de la campaña no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio debe ser hoy o una fecha posterior.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
        ];
    }
}
