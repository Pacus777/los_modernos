<?php

namespace Tests\Unit;

use App\Support\RedesSocialesEmprendedor;
use PHPUnit\Framework\TestCase;

class RedesSocialesEmprendedorTest extends TestCase
{
    public function test_whatsapp_desde_ocho_digitos_locales(): void
    {
        $url = RedesSocialesEmprendedor::urlWhatsApp('71234567');

        $this->assertSame('https://wa.me/59171234567', $url);
    }

    public function test_whatsapp_normaliza_con_codigo_591(): void
    {
        $guardado = RedesSocialesEmprendedor::normalizarWhatsAppGuardado('+591 71234567');

        $this->assertSame('59171234567', $guardado);
    }

    public function test_whatsapp_acepta_cero_inicial_nacional(): void
    {
        $guardado = RedesSocialesEmprendedor::normalizarWhatsAppGuardado('071234567');

        $this->assertSame('59171234567', $guardado);
    }

    public function test_whatsapp_rechaza_celular_invalido(): void
    {
        $this->assertFalse(RedesSocialesEmprendedor::esWhatsAppBoliviaValido('51234567'));
        $this->assertFalse(RedesSocialesEmprendedor::esWhatsAppBoliviaValido('12345'));
    }

    public function test_instagram_agrega_https_si_falta(): void
    {
        $url = RedesSocialesEmprendedor::urlGenerica('instagram.com/wayna');

        $this->assertSame('https://instagram.com/wayna', $url);
    }

    public function test_tiktok_en_para_frontend(): void
    {
        $redes = RedesSocialesEmprendedor::paraFrontend(
            null,
            null,
            null,
            'tiktok.com/@waynamercados',
        );

        $this->assertSame('https://tiktok.com/@waynamercados', $redes['tiktok']);
    }

    public function test_sitio_web_en_para_frontend(): void
    {
        $redes = RedesSocialesEmprendedor::paraFrontend(
            null,
            null,
            null,
            null,
            'mi-tienda.bo',
        );

        $this->assertSame('https://mi-tienda.bo', $redes['sitio_web']);
    }
}
