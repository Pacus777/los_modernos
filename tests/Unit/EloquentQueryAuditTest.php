<?php

namespace Tests\Unit;

use App\Support\EloquentQueryAudit;
use PHPUnit\Framework\TestCase;

class EloquentQueryAuditTest extends TestCase
{
    public function test_inventario_de_queries_raw_documentado(): void
    {
        $hallazgos = EloquentQueryAudit::hallazgosRevisados();

        $this->assertNotEmpty($hallazgos);
        $this->assertTrue(EloquentQueryAudit::sinHallazgosCriticos());

        $archivos = array_column($hallazgos, 'archivo');

        $this->assertContains('app/Services/EmprendedorExplorarService.php', $archivos);
    }
}
