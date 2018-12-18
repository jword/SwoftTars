<?php
return [
    'servicePacker'     => [
        'defaultPacker' => defined('RPC_PROTOCOL') ? RPC_PROTOCOL : 'json',
        'packers'       => [
            'tars'       => \App\Lib\Tars\Server\TarsPacker::class,
            'tarsclient' => \App\Lib\Tars\Client\TarsPacker::class,
        ],
    ],
    'ServiceDispatcher' => [
        'middlewares' => [
            \App\Lib\Tars\Server\ResultDealMiddleware::class,
        ],
    ],
];
