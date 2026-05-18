<?php
// Маршруты
$routes = [
    'GET' => [
        '/' => 'HomeController@index',
        '/auth/google' => 'AuthController@googleAuth',
        '/auth/google-callback' => 'AuthController@googleCallback',
    ],
    'POST' => [
        '/api/register' => 'AuthController@register',
        '/api/login' => 'AuthController@login',
        '/api/translate' => 'TranslationController@translate',
        '/api/translate-image' => 'ImageController@translate',
        '/api/convert-image' => 'ImageController@convert',
        '/api/convert-audio' => 'AudioController@convert',
        '/api/cut-audio' => 'AudioController@cut',
    ]
];

return $routes;