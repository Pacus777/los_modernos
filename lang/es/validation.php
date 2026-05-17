<?php

return [

    'required' => 'Ingresá tu :attribute.',
    'email' => 'El :attribute debe ser un correo válido (ejemplo: nombre@correo.com).',
    'string' => 'El :attribute debe ser texto.',

    'numeric' => 'El campo :attribute debe ser un número.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'exists' => 'El :attribute seleccionado no es válido.',

    'attributes' => [
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'campana_id' => 'campaña',
        'tipo_pago_id' => 'método de pago',
        'visitante_nombre' => 'nombre',
        'monto' => 'monto',
        'metodo' => 'método',
        'referencia_pago' => 'referencia de pago',
    ],

];
