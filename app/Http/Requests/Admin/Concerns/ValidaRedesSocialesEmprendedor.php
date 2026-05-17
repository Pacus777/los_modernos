<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Support\RedesSocialesEmprendedor;

trait ValidaRedesSocialesEmprendedor
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('whatsapp')) {
            return;
        }

        $whatsapp = $this->input('whatsapp');

        $this->merge([
            'whatsapp' => RedesSocialesEmprendedor::normalizarWhatsAppGuardado(
                is_string($whatsapp) ? $whatsapp : null,
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function reglasRedesSocialesEmprendedor(): array
    {
        return [
            'whatsapp' => [
                'nullable',
                'string',
                'max:120',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }

                    if (! RedesSocialesEmprendedor::esWhatsAppBoliviaValido((string) $value)) {
                        $fail(
                            'Ingresá un celular boliviano de 8 dígitos (empieza en 6 o 7), con o sin código +591.',
                        );
                    }
                },
            ],
            'instagram' => ['nullable', 'string', 'max:500'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'tiktok' => ['nullable', 'string', 'max:500'],
            'sitio_web' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesRedesSocialesEmprendedor(): array
    {
        return [
            'whatsapp.max' => 'El número de WhatsApp es demasiado largo.',
            'instagram.max' => 'El enlace de Instagram es demasiado largo.',
            'facebook.max' => 'El enlace de Facebook es demasiado largo.',
            'tiktok.max' => 'El enlace de TikTok es demasiado largo.',
            'sitio_web.max' => 'El enlace del sitio web es demasiado largo.',
        ];
    }
}
