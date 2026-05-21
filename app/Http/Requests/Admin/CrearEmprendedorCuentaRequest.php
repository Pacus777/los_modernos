<?php

namespace App\Http\Requests\Admin;

use App\Models\Emprendedor;
use App\Rules\CorreoGmail;
use App\Support\CorreoGmailEmprendedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrearEmprendedorCuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('admin') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Emprendedor $emprendedor */
        $emprendedor = $this->route('emprendedor');

        $reglasEmail = [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            Rule::unique('users', 'email'),
        ];

        if (CorreoGmailEmprendedor::exigido()) {
            $reglasEmail[] = new CorreoGmail;
        }

        return [
            'email' => $reglasEmail,
            'name' => ['nullable', 'string', 'max:255'],
            'finalizar_registro' => ['sometimes', 'boolean'],
            'desde_listado' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Indicá el correo del emprendedor.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ese correo ya está registrado en el sistema.',
        ];
    }
}
