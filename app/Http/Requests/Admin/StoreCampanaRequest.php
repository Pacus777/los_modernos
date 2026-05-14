<?php

namespace App\Http\Requests\Admin;

use App\Models\Campana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampanaRequest extends FormRequest
{
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
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
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
            $ini = $this->input('fecha_inicio');
            $fin = $this->input('fecha_fin');
            if ($ini && $fin && strtotime((string) $fin) < strtotime((string) $ini)) {
                $validator->errors()->add(
                    'fecha_fin',
                    'La fecha de fin debe ser igual o posterior a la de inicio.'
                );
            }

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
        ];
    }
}
