<?php

namespace Database\Seeders;

use App\Models\Emprendedor;
use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use Illuminate\Database\Seeder;

class EmprendedorSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para poblar datos de prueba de emprendedores.
     */
    public function run(): void
    {
        $emprendedores = [
            [
                'nombre' => 'Juan Carlos',
                'apellidos' => 'Pérez Quispe',
                'descripcion' => 'Artesano especializado en tallado en madera y réplicas de arte precolombino andino. Utiliza técnicas ancestrales heredadas de su familia.',
                'tipo_emprendimiento' => TipoEmprendimiento::Artesania,
                'departamento' => Departamento::LaPaz,
                'fotografia' => 'emprendedores/fotos/juan-perez.jpg',
                'whatsapp' => '+59171234567',
                'facebook' => 'https://facebook.com/artesanias.perez',
                'estado' => 'activo',
                'meta_monto' => 5000.00,
            ],
            [
                'nombre' => 'María Elena',
                'apellidos' => 'Flores Condori',
                'descripcion' => 'Cafetería y repostería artesanal que utiliza granos de café 100% bolivianos provenientes de los Yungas. Famosa por sus empanadas de queso de cabra.',
                'tipo_emprendimiento' => TipoEmprendimiento::Gastronomia,
                'departamento' => Departamento::Cochabamba,
                'fotografia' => 'emprendedores/fotos/maria-flores.jpg',
                'whatsapp' => '+59161234568',
                'instagram' => 'https://instagram.com/yungas.cafe.bo',
                'estado' => 'activo',
                'meta_monto' => 12000.00,
            ],
            [
                'nombre' => 'Carlos Mauricio',
                'apellidos' => 'Mamani Vargas',
                'descripcion' => 'Guía de turismo comunitario y ecoturismo en el Salar de Uyuni. Ofrece experiencias inmersivas y visitas a islas de cactus gigantes poco conocidas.',
                'tipo_emprendimiento' => TipoEmprendimiento::Turismo,
                'departamento' => Departamento::Potosi,
                'fotografia' => 'emprendedores/fotos/carlos-mamani.jpg',
                'whatsapp' => '+59178765432',
                'sitio_web' => 'https://uyuni-comunitario.bo',
                'estado' => 'activo',
                'meta_monto' => 8500.00,
            ],
            [
                'nombre' => 'Ana Beatriz',
                'apellidos' => 'Gutiérrez Mendoza',
                'descripcion' => 'Taller de tejido textil tradicional con lana de alpaca y tintes naturales. Sus diseños fusionan la iconografía tradicional con cortes modernos.',
                'tipo_emprendimiento' => TipoEmprendimiento::Textil,
                'departamento' => Departamento::Oruro,
                'fotografia' => 'emprendedores/fotos/ana-gutierrez.jpg',
                'whatsapp' => '+59170123456',
                'tiktok' => 'https://tiktok.com/@alpacatejidos.bo',
                'estado' => 'activo',
                'meta_monto' => 15000.00,
            ]
        ];

        foreach ($emprendedores as $datos) {
            Emprendedor::updateOrCreate(
                [
                    'nombre' => $datos['nombre'],
                    'apellidos' => $datos['apellidos'],
                ],
                $datos
            );
        }
    }
}
