<?php

return [
    'custom' => [
        'email' => [
            'required' => 'Заполните Email',
            'email' => 'Введите корректный email',
            'unique' => 'Пользователь с таким email уже существует'
        ],
        'name' => [
            'required' => 'Заполните Имя',
        ],
        'rate' => [
            'required' => 'Заполните Rate',
            'integer' => 'Для Rate введите цифры'
        ],
        'planned_hours.*' => [
            'required' => 'Запланированные часы должны быть заполнены.',
            'integer' => 'Запланированные часы должны быть целыми числами.',
            'between' => 'Запланированные часы должны быть от :min до :max.',
        ]

    ],
    'min' => [
        'numeric' => 'Должен быть не менее :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'Должен быть не менее :min символов.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'confirmed' => 'Подтверждение не совпадает',
];
