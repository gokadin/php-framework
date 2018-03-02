<?php

return [
    'models' => [
        'user' => [
            'role' => 'admin',
            'access' => '/**'
        ]
    ],

    'routes' => [
        'login' => '/login',
        'logout' => '/logout'
    ]
];