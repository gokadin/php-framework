<?php

return [
    'user' => [
        'name' => ['type' => 'string'],
        'posts' => ['hasMany' => 'post']
    ]
];