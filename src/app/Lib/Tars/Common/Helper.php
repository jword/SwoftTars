<?php

namespace App\Lib\Tars\Common;

use App\Lib\Tars\Common\TarsDefinition;
use App\Lib\Tars\Common\TARSProtocol;

/**
 * TarsHelper
 */
class Helper
{
    private static $protocol;

    public static function getDefineByInterface($interface, $func)
    {
        if (!isset(TarsDefinition::$servicemap[$interface])) {
            throw new \Exception("接口{$interface}的定义信息不存在", 600);
        }
        $servant = TarsDefinition::$servicemap[$interface];
        return TarsDefinition::$definitions[$servant][$func];
    }

    public static function getDefineByServant($servant, $func)
    {
        if (!isset(TarsDefinition::$definitions[$servant])) {
            throw new \Exception("servant {$servant} 的定义信息不存在", 600);
        }
        return TarsDefinition::$definitions[$servant][$func];
    }

    public static function getServantByInterface($interface)
    {
        if (!isset(TarsDefinition::$servicemap[$interface])) {
            throw new \Exception("接口{$interface}的定义信息不存在", 600);
        }

        return TarsDefinition::$servicemap[$interface];
    }

    public static function getInterfaceByServant($servant)
    {
        $interface = array_search($servant, TarsDefinition::$servicemap);
        if (empty($interface)) {
            throw new \Exception("接口{$interface}的定义信息不存在", 600);
        }

        return $interface;
    }

    public static function getProtocol()
    {
        if (!is_object(self::$protocol)) {
            self::$protocol = new TARSProtocol();
        }

        return self::$protocol;
    }
}
