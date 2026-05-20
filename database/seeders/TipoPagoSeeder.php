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
                'proveedor' => 'manual',
                'requiere_validacion_manual' => true,
            ],
            [
                'codigo' => 'qr',
                'nombre' => 'QR / billetera móvil',
                'descripcion' => 'Pago escaneando QR o app de billetera.',
                'proveedor' => 'banco',
                'requiere_validacion_manual' => false,
            ],
            [
                'codigo' => 'tarjeta',
                'nombre' => 'Tarjeta',
                'descripcion' => 'Tarjeta débito o crédito.',
                'proveedor' => 'libelula',
                'requiere_validacion_manual' => false,
            ],
            [
                'codigo' => 'transferencia',
                'nombre' => 'Transferencia bancaria',
                'descripcion' => 'Transferencia o depósito bancario.',
                'proveedor' => 'banco',
                'requiere_validacion_manual' => false,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoPago::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                [
                    'nombre' => $tipo['nombre'],
                    'descripcion' => $tipo['descripcion'],
                    'proveedor' => $tipo['proveedor'],
                    'requiere_validacion_manual' => $tipo['requiere_validacion_manual'],
                    'activo' => true,
                ]
            );
        }
    }
}
