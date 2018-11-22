<?php
return [
    'noticeHandler'      => [
        'class'     => \App\Lib\Logger::class,
        'logFile'   => '@runtime/logs/notice.log',
        'formatter' => '${lineFormatter}',
        'levels'    => [
            \Swoft\Log\Logger::NOTICE,
            //\Swoft\Log\Logger::INFO,
            //\Swoft\Log\Logger::DEBUG,
            //\Swoft\Log\Logger::TRACE,
        ],
    ],
    'applicationHandler' => [
        'class'     => \App\Lib\Logger::class,
        'logFile'   => '@runtime/logs/error.log',
        'formatter' => '${lineFormatter}',
        'levels'    => [
            \Swoft\Log\Logger::ERROR,
            \Swoft\Log\Logger::WARNING,
        ],
    ],
    'logger'             => [
        'name'          => APP_NAME,
        'enable'        => false,
        'flushInterval' => 100,
        'flushRequest'  => true,
        'handlers'      => [
            //'${noticeHandler}',
            //'${applicationHandler}',
        ],
    ],
];
