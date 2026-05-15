<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmprendedorRequest extends FormRequest
{
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
            'descripcion' => ['required', 'string', 'min:10', 'max:5000'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'meta_monto' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],

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
            'meta_monto.required' => 'La meta económica es obligatoria.',
            'meta_monto.numeric' => 'La meta económica debe ser un número.',
            'meta_monto.min' => 'La meta debe ser mayor a cero.',
            'descripcion.required' => 'La descripción del emprendimiento es obligatoria.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 5000 caracteres.',
            'fotografia.image' => 'El archivo debe ser una imagen válida.',
            'fotografia.mimes' => 'La fotografía debe estar en formato JPG, JPEG, PNG o WEBP.',
            'fotografia.max' => 'La fotografía no debe pesar más de 2 MB.',
        ];
    }
}