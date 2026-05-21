<?php

namespace App\Http\Requests\Emprendedor;

use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Http\Requests\Admin\Concerns\ValidaMediosEmprendedor;
use App\Http\Requests\Admin\Concerns\ValidaRedesSocialesEmprendedor;
use App\Http\Requests\Concerns\SanitizesTextInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * E-06 — Campos del perfil público que el emprendedor puede editar (sin estado ni meta admin).
 */
class UpdatePerfilPublicoRequest extends FormRequest
{
    use SanitizesTextInput;
    use ValidaMediosEmprendedor;
    use ValidaRedesSocialesEmprendedor {
        prepareForValidation as protected prepareWhatsAppRedes;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeFields(['nombre', 'apellidos', 'descripcion']);
        $this->prepareWhatsAppRedes();
    }

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
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'min:10', 'max:5000'],
            'tipo_emprendimiento' => ['required', 'string', Rule::in(TipoEmprendimiento::valores())],
            'departamento' => ['required', 'string', Rule::in(Departamento::valores())],
            'fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ...$this->reglasMediosEmprendedor(),
            ...$this->reglasRedesSocialesEmprendedor(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'Tu nombre es obligatorio.',
            'apellidos.required' => 'Tus apellidos son obligatorios.',
            'tipo_emprendimiento.required' => 'Debés elegir el tipo de emprendimiento.',
            'tipo_emprendimiento.in' => 'El tipo de emprendimiento seleccionado no es válido.',
            'departamento.required' => 'Debés elegir el departamento.',
            'departamento.in' => 'El departamento seleccionado no es válido.',
            'descripcion.required' => 'La descripción de tu emprendimiento es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 5000 caracteres.',
            'fotografia.image' => 'El archivo debe ser una imagen válida.',
            'fotografia.mimes' => 'La fotografía debe estar en formato JPG, JPEG, PNG o WEBP.',
            'fotografia.max' => 'La fotografía no debe pesar más de 2 MB.',
            ...$this->mensajesMediosEmprendedor(),
            ...$this->mensajesRedesSocialesEmprendedor(),
        ];
    }
}
