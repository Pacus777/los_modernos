<?php

namespace Database\Seeders;

use App\Models\Campana;
use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorSeguidor;
use App\Models\TuristaNotificacion;
use App\Models\User;
use App\Models\Visitante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TuristaTestSeeder extends Seeder
{
    /**
     * S4-09: Seeder para crear un turista con cuenta y notificaciones de prueba.
     */
    public function run(): void
    {
        $email = 'turista@wayna.test';
        $password = 'password';

        // 1. Limpiar datos existentes
        User::query()->where('email', $email)->delete();
        Visitante::query()->where('codigo', 'VIS-TURISTA-SEED')->delete();

        // 2. Crear Turista con cuenta (sin rol)
        $user = User::query()->create([
            'name' => 'Ana Gómez (Turista)',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // 3. Crear Visitante asociado
        $visitante = Visitante::query()->create([
            'codigo' => 'VIS-TURISTA-SEED',
            'nombre' => 'Ana Gómez',
            'idioma' => 'es',
        ]);

        // 4. Buscar un emprendedor activo para vincular el seguimiento
        $emprendedor = Emprendedor::query()->where('estado', 'activo')->first();

        if ($emprendedor) {
            // Vincular seguidor confirmado
            $seguimiento = EmprendedorSeguidor::query()->create([
                'emprendedor_id' => $emprendedor->id,
                'visitante_id' => $visitante->id,
                'email' => $email,
                'confirmado_en' => now(),
                'token_unsub' => Str::random(40),
            ]);

            // 5. Crear una publicación y su notificación
            $post = EmprendedorPost::query()->create([
                'emprendedor_id' => $emprendedor->id,
                'tipo' => 'texto',
                'estado' => \App\Enums\EmprendedorPostEstado::Publicado,
                'contenido' => '¡Hola a todos! Hemos subido nuevas fotos de nuestros talleres.',
                'publicado_en' => now(),
            ]);

            TuristaNotificacion::query()->create([
                'user_id' => $user->id,
                'emprendedor_id' => $emprendedor->id,
                'emprendedor_post_id' => $post->id,
                'tipo' => TuristaNotificacion::TIPO_NUEVO_POST,
                'titulo' => 'Nueva publicación',
                'mensaje' => "{$emprendedor->nombreCompleto()} compartió una nueva publicación.",
                'url' => route('turista.emprendedor.show', $emprendedor->id),
            ]);

            // 6. Crear una meta y su notificación
            $campana = Campana::query()->create([
                'emprendedor_id' => $emprendedor->id,
                'titulo' => 'Comprar herramientas de tallado',
                'meta_apoyo' => 4500.00,
                'estado' => Campana::ESTADO_ACTIVA,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addMonth(),
            ]);

            TuristaNotificacion::query()->create([
                'user_id' => $user->id,
                'emprendedor_id' => $emprendedor->id,
                'campana_id' => $campana->id,
                'tipo' => TuristaNotificacion::TIPO_NUEVA_META,
                'titulo' => 'Nueva meta de apoyo',
                'mensaje' => "{$emprendedor->nombreCompleto()} publicó una nueva meta: Comprar herramientas de tallado.",
                'url' => route('turista.emprendedor.show', $emprendedor->id),
            ]);
        }
    }
}
