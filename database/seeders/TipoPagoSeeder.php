<?php

namespace Database\Seeders;

use App\Models\TipoPago;
use Illuminate\Database\Seeder;

class TipoPagoSeeder extends Seeder
{
    /**
     * Tipos de pago base para pruebas e integración futura (donaciones / cajero).
     */
    public function run(): void
    {
        $tipos = [
            [
                'codigo' => 'efectivo',
                'nombre' => 'Efectivo',
                'descripcion' => 'Pago en efectivo en punto de venta o caja.',
            ],
            [
                'codigo' => 'qr',
                'nombre' => 'QR / billetera móvil',
                'descripcion' => 'Pago escaneando QR o app de billetera.',
            ],
            [
                'codigo' => 'tarjeta',
                'nombre' => 'Tarjeta',
                'descripcion' => 'Tarjeta débito o crédito.',
            ],
            [
                'codigo' => 'transferencia',
                'nombre' => 'Transferencia bancaria',
                'descripcion' => 'Transferencia o depósito bancario.',
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoPago::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                [
                    'nombre' => $tipo['nombre'],
                    'descripcion' => $tipo['descripcion'],
                    'activo' => true,
                ]
            );
        }
    }
}
