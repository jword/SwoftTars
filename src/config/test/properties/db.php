<?php
return [
    'master' => [
        'name'        => 'master',
        'uri'         => [
            '127.0.0.1:3306/seo?user=root&password=123456&charset=utf8',
        ],
        'minActive'   => 8,
        'maxActive'   => 500,
        'maxWait'     => 8,
        'timeout'     => 8,
        'maxIdleTime' => 60,
        'maxWaitTime' => 3,
    ],

    'slave'  => [
        'name'        => 'slave',
        'uri'         => [
            '127.0.0.1:3306/seo?user=root&password=123456&charset=utf8',
        ],
        'minActive'   => 8,
        'maxActive'   => 500,
        'maxWait'     => 8,
        'timeout'     => 8,
        'maxIdleTime' => 60,
        'maxWaitTime' => 3,
    ],
];
