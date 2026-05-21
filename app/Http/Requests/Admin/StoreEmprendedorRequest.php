<?php

namespace App\Http\Requests\Admin;

use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Http\Requests\Admin\Concerns\ValidaMediosEmprendedor;
use App\Http\Requests\Admin\Concerns\ValidaRedesSocialesEmprendedor;
use App\Http\Requests\Concerns\SanitizesTextInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmprendedorRequest extends FormRequest
{
    use SanitizesTextInput;
    use ValidaMediosEmprendedor;
    use ValidaRedesSocialesEmprendedor;

    protected function prepareForValidation(): void
    {
        $this->sanitizeFields(['nombre', 'apellidos', 'descripcion']);

        if (! $this->filled('meta_monto')) {
            $this->merge(['meta_monto' => 0]);
        }

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
     * La seguridad principal ya la controla:
     * - auth
     * - verified
     * - check.role:admin
     *
     * Por eso aquí devolvemos true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para crear un emprendedor.
     *
     * La fotografía es opcional por ahora, pero si se envía:
     * - debe ser una imagen
     * - debe ser jpg, jpeg, png o webp
     * - no debe pesar más de 2 MB
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
            'meta_monto' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],

            /*
            |--------------------------------------------------------------------------
            | Validación de fotografía
            |--------------------------------------------------------------------------
            |
            | max:2048 significa máximo 2048 KB, es decir, 2 MB.
            | No usamos archivos muy grandes para evitar lentitud en el sistema.
            |
            */
            'fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ...$this->reglasMediosEmprendedor(),
            ...$this->reglasRedesSocialesEmprendedor(),
        ];
    }

    /**
     * Mensajes personalizados para que el usuario entienda mejor los errores.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del emprendedor es obligatorio.',
            'apellidos.required' => 'Los apellidos del emprendedor son obligatorios.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'meta_monto.numeric' => 'La meta económica debe ser un número.',
            'meta_monto.min' => 'La meta no puede ser negativa.',
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