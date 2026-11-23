<?php
// Modelo de configuração. Copiar para app/config.php e preencher.
// app/config.php não vai para o Git (tem credenciais).
declare(strict_types=1);

return [
    'env' => 'local',                 // local | staging | production
    'base_url' => 'http://localhost:8080',

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'admsc',
        'user' => 'admsc',
        'pass' => '',
    ],
];
