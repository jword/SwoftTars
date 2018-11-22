<?php
return [
    'redis' => [
        'name'        => 'redis',
        'uri'         => [
            '127.0.0.1:9221',
        ],
        'minActive'   => 8,
        'maxActive'   => 500,
        'maxWait'     => 8,
        'maxWaitTime' => 3,
        'maxIdleTime' => 60,
        'timeout'     => 8,
        'db'          => 1,
        'serialize'   => 0,
    ],
];
