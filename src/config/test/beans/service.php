<?php
return [
    'servicePacker'     => [
        'defaultPacker' => RPC_PROTOCOL == 'tars' ? 'tars' : 'json',
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
