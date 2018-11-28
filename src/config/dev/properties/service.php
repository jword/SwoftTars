<?php
return [
    'user'    => [
        'name'        => 'user',
        'uri'         => [
            '127.0.0.1:8099',
        ],
        'minActive'   => 8,
        'maxActive'   => 1000,
        'maxWait'     => 80,
        'maxWaitTime' => 3600,
        'maxIdleTime' => 60,
        'timeout'     => 8,
        'useProvider' => false,
        'balancer'    => 'random',
        'provider'    => 'consul',
    ],
    'tars'    => [
        'name'        => 'tars',
        'uri'         => [
            '127.0.0.1:8099',
        ],
        'minActive'   => 8,
        'maxActive'   => 1000,
        'maxWait'     => 80,
        'maxWaitTime' => 3600,
        'maxIdleTime' => 60,
        'timeout'     => 8,
        'useProvider' => false,
        'balancer'    => 'random',
        'provider'    => 'consul',
        'packer'      => 'tarsclient',
    ],
    'default' => [
        'name'        => 'default',
        'uri'         => [
            '127.0.0.1:8099',
        ],
        'minActive'   => 8,
        'maxActive'   => 50,
        'maxWait'     => 80,
        'maxWaitTime' => 3600,
        'maxIdleTime' => 60,
        'timeout'     => 8,
        'useProvider' => false,
        'balancer'    => 'random',
        'provider'    => 'consul',
    ],
];
