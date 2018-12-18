<?php
return [
    'version'      => '1.0',
    'autoInitBean' => true,
    'bootScan'     => [
        'App\Commands',
        'App\Boot',
        'App\Models',
    ],
    'beanScan'     => [
        'App\Middlewares',
        'App\Tasks',
        'App\Exception',
        'App\Listener',
        'App\Process',
        'App\Fallback',
    ],
];
