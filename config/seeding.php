<?php

/**
 * Valores usados solo por los seeders de desarrollo.
 * Las contraseñas y correos deben definirse en .env (ver .env.example).
 * No coloques secretos reales aquí.
 */
return [

    'admin' => [
        'email' => env('SEED_ADMIN_EMAIL', 'admin@wayna.test'),
        'name' => env('SEED_ADMIN_NAME', 'Administrador WAYNA'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],

    'cajero' => [
        'email' => env('SEED_CAJERO_EMAIL', 'cajero@wayna.test'),
        'name' => env('SEED_CAJERO_NAME', 'Cajero WAYNA'),
        'password' => env('SEED_CAJERO_PASSWORD'),
    ],

    'sin_rol' => [
        'email' => env('SEED_SIN_ROL_EMAIL', 'sinrol@wayna.test'),
        'name' => env('SEED_SIN_ROL_NAME', 'Usuario Sin Rol'),
        'password' => env('SEED_SIN_ROL_PASSWORD'),
    ],

];
