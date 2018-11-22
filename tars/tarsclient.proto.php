<?php
return array(
    'appName'         => 'SwoftDemo',
    'serverName'      => 'Demo',
    'objName'         => 'TarsDemo',
    'withServant'     => false, //决定是服务端,还是客户端的自动生成
    'tarsFiles'       => array(
        './obj/TarsDemo.tars', //用户对象
    ),
    'dstPath'         => '../src/app/TarsClient',
    'namespacePrefix' => 'App\TarsClient',
);
