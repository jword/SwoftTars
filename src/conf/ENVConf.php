<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/17
 * Time: 下午4:15.
 */

namespace HttpServer\conf;

class ENVConf
{
    public static $locator
    = 'tars.tarsregistry.QueryObj@tcp -h 192.168.11.247 -p 17890';

    public static $logPath = '/usr/local/app/tars/app_log/SwoftDemo/Demo';

    public static $socketMode = 2;

    public static function getTarsConf()
    {
        $table    = $_SERVER->table;
        $result   = $table->get('tars:php:tarsConf');
        $tarsConf = unserialize($result['tarsConfig']);

        return $tarsConf;
    }
}
