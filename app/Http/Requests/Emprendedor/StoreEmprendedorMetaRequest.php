<?php

namespace App\Http\Requests\Emprendedor;

use App\Http\Requests\Admin\Concerns\ValidaFechasCampana;
use App\Models\Campana;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmprendedorMetaRequest extends FormRequest
{
    use ValidaFechasCampana;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Campana::class) ?? false;
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
            'fecha_inicio' => ['required', 'date', 'after_or_equal:today'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validarRangoFechasCampana($validator);
            $this->validarCampanaActivaVigente($validator);

            $emprendedorId = $this->user()?->emprendedor?->id;

            if (! $emprendedorId) {
                return;
            }

            $existe = Campana::query()
                ->where('emprendedor_id', $emprendedorId)
                ->where('estado', Campana::ESTADO_ACTIVA)
                ->exists();

            if ($existe) {
                $validator->errors()->add(
                    'titulo',
                    'Ya tenés una campaña activa. Editá la actual o cerrala antes de crear otra.',
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título de tu meta es obligatorio.',
            'meta_apoyo.required' => 'Indicá el monto que querés recaudar (Bs).',
            'meta_apoyo.min' => 'La meta debe ser mayor a cero.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
        ];
    }
}
