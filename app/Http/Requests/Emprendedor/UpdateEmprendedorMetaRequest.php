<?php

namespace App\Http\Requests\Emprendedor;

use App\Http\Requests\Admin\Concerns\ValidaFechasCampana;
use App\Models\Campana;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmprendedorMetaRequest extends FormRequest
{
    use ValidaFechasCampana;

    public function authorize(): bool
    {
        /** @var Campana|null $campana */
        $campana = $this->route('campana');

        return $campana && ($this->user()?->can('update', $campana) ?? false);
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'fecha_inicio' => $this->filled('fecha_inicio') ? $this->input('fecha_inicio') : null,
            'fecha_fin' => $this->filled('fecha_fin') ? $this->input('fecha_fin') : null,
            'estado' => Campana::ESTADO_ACTIVA,
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'meta_apoyo' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Campana $campana */
            $campana = $this->route('campana');

            $this->validarRangoFechasCampana($validator);
            $this->validarFechaInicioNoRetroactivaEnEdicion($validator, $campana);
            $this->validarCampanaActivaVigente($validator);
        });
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título de tu meta es obligatorio.',
            'meta_apoyo.required' => 'Indicá el monto que querés recaudar (Bs).',
            'meta_apoyo.min' => 'La meta debe ser mayor a cero.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
        ];
    }
}
