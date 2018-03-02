<?php

return [

    'mappingDriver' => 'annotation',

    'databaseDriver' => 'mysql',

    'mysql' => [
        'host' => getenv('DATABASE_HOST'),
        'database' => getenv('DATABASE_NAME'),
        'username' => getenv('DATABASE_USERNAME'),
        'password' => getenv('DATABASE_PASSWORD')
    ],

    'classes' => [

    ]

];