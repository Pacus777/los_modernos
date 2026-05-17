<?php

namespace Tests\Unit;

use App\Support\RedesSocialesEmprendedor;
use PHPUnit\Framework\TestCase;

class RedesSocialesEmprendedorTest extends TestCase
{
    public function test_whatsapp_desde_numero(): void
    {
        $url = RedesSocialesEmprendedor::urlWhatsApp('591 70000000');

        $this->assertSame('https://wa.me/59170000000', $url);
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
}
