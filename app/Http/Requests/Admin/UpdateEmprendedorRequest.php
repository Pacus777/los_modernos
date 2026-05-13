<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmprendedorRequest extends FormRequest
{
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
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'meta_monto' => ['required', 'numeric', 'min:0'],
            'fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'meta_monto.required' => 'La meta económica es obligatoria.',
            'meta_monto.numeric' => 'La meta económica debe ser un número.',
            'fotografia.image' => 'El archivo debe ser una imagen válida.',
            'fotografia.mimes' => 'La fotografía debe estar en formato JPG, JPEG, PNG o WEBP.',
            'fotografia.max' => 'La fotografía no debe pesar más de 2 MB.',
        ];
    }
}