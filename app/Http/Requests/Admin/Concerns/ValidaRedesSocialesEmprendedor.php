<?php

namespace App\Http\Requests\Admin\Concerns;

trait ValidaRedesSocialesEmprendedor
{
    /**
     * @return array<string, mixed>
     */
    protected function reglasRedesSocialesEmprendedor(): array
    {
        return [
            'whatsapp' => ['nullable', 'string', 'max:120'],
            'instagram' => ['nullable', 'string', 'max:500'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'tiktok' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesRedesSocialesEmprendedor(): array
    {
        return [
            'whatsapp.max' => 'El contacto de WhatsApp es demasiado largo.',
            'instagram.max' => 'El enlace de Instagram es demasiado largo.',
            'facebook.max' => 'El enlace de Facebook es demasiado largo.',
            'tiktok.max' => 'El enlace de TikTok es demasiado largo.',
        ];
    }
}
