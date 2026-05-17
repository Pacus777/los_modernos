<?php

return [

    'required' => 'Please enter your :attribute.',
    'email' => 'The :attribute must be a valid email address (e.g. name@email.com).',
    'string' => 'The :attribute must be text.',

    'numeric' => 'The :attribute must be a number.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
    ],
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'string' => 'The :attribute must not be greater than :max characters.',
    ],
    'exists' => 'The selected :attribute is invalid.',

    'attributes' => [
        'email' => 'email address',
        'password' => 'password',
        'campana_id' => 'campaign',
        'tipo_pago_id' => 'payment method',
        'visitante_nombre' => 'name',
        'monto' => 'amount',
        'metodo' => 'method',
        'referencia_pago' => 'payment reference',
    ],

];
