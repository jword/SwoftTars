<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/2/24
 * Time: 下午3:43.
 */

return array(
    'appName'         => 'SwoftDemo',
    'serverName'      => 'Demo',
    'objName'         => 'TarsDemo',
    'withServant'     => true, //决定是服务端,还是客户端的自动生成
    'tarsFiles'       => array(
        './obj/TarsDemo.tars', //用户对象
    ),
    'dstPath'         => '../src/app/Lib',
    'namespacePrefix' => 'App\Lib',
);
