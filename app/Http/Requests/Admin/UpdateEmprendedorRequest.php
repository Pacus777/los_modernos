<?php

namespace App\Http\Requests\Admin;

use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Http\Requests\Admin\Concerns\ValidaMediosEmprendedor;
use App\Http\Requests\Admin\Concerns\ValidaRedesSocialesEmprendedor;
use App\Http\Requests\Concerns\SanitizesTextInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmprendedorRequest extends FormRequest
{
    use SanitizesTextInput;
    use ValidaMediosEmprendedor;
    use ValidaRedesSocialesEmprendedor;

    protected function prepareForValidation(): void
    {
        $this->sanitizeFields(['nombre', 'apellidos', 'descripcion']);

        if (! $this->filled('descripcion')) {
            $this->merge(['descripcion' => null]);
        }

        if ($this->has('whatsapp')) {
            $this->merge([
                'whatsapp' => \App\Support\RedesSocialesEmprendedor::normalizarWhatsAppGuardado(
                    is_string($this->input('whatsapp')) ? $this->input('whatsapp') : null,
                ),
            ]);
        }
    }
    /**
     * Autoriza esta solicitud.
     *
     * El acceso lo protegen los middleware de las rutas administrativas.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualizar un emprendedor.
     *
     * La fotografía sigue siendo opcional.
     * Si el admin no sube una nueva imagen, se conserva la anterior.
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'tipo_emprendimiento' => ['required', 'string', Rule::in(TipoEmprendimiento::valores())],
            'departamento' => ['required', 'string', Rule::in(Departamento::valores())],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ...$this->reglasMediosEmprendedor(),
            ...$this->reglasRedesSocialesEmprendedor(),
        ];
    }

    /**
     * Mensajes personalizados para errores de validación.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del emprendedor es obligatorio.',
            'apellidos.required' => 'Los apellidos del emprendedor son obligatorios.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'tipo_emprendimiento.required' => 'Debés elegir el tipo de emprendimiento.',
            'tipo_emprendimiento.in' => 'El tipo de emprendimiento seleccionado no es válido.',
            'departamento.required' => 'Debés elegir el departamento.',
            'departamento.in' => 'El departamento seleccionado no es válido.',
            'descripcion.max' => 'La descripción no puede superar los 5000 caracteres.',
            'fotografia.image' => 'El archivo debe ser una imagen válida.',
            'fotografia.mimes' => 'La fotografía debe estar en formato JPG, JPEG, PNG o WEBP.',
            'fotografia.max' => 'La fotografía no debe pesar más de 2 MB.',
            ...$this->mensajesMediosEmprendedor(),
            ...$this->mensajesRedesSocialesEmprendedor(),
        ];
    }
}