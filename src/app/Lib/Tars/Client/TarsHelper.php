<?php

namespace App\Lib\Tars\Client;

use App\Lib\Tars\Client\TarsDefinition;
use Tars\client\TUPAPIWrapper;

/**
 * TarsHelper
 */
class TarsHelper
{
    private static $protocol;
    /**
     * 打包请求数据
     */
    public static function pack($data, $iVersion)
    {
        $definitions = self::getDefineByInterface($data['interface']);
        $inParams    = $definitions['methods'][$data['method']]['inParams'];
        $encodeBufs  = [];
        foreach ($inParams as $param) {
            $type = $param['type'];

            $packMethod = Utils::getPackMethods($type);
            $valueName  = $param['name'];
            $index      = $param['tag'];
            $paramvalue = $data['params'][$index - 1];
            // 判断如果是vector需要特别的处理
            if (Utils::isVector($type)) {
                $vec = self::getProtocol()->createInstance($param['proto']);
                foreach ($paramvalue as $value) {
                    $vec->pushBack($value);
                }
                $__buffer = TUPAPIWrapper::$packMethod($valueName, $index, $vec, $iVersion);
            } elseif (Utils::isMap($type)) {
                $map = self::getProtocol()->createInstance($param['proto']);
                foreach ($paramvalue as $key => $value) {
                    $map->pushBack([$key => $value]);
                }
                $__buffer = TUPAPIWrapper::$packMethod($valueName, $index, $map, $iVersion);
            } else {
                $__buffer = TUPAPIWrapper::$packMethod($valueName, $index, $paramvalue, $iVersion);
            }
            $encodeBufs[$valueName] = $__buffer;
        }
        return $encodeBufs;
    }

    /**
     * 解包返回数据
     * @author likunlun@gongchang.com
     * @param  [type]                   $outParams [description]
     * @return [type]                              [description]
     */
    public static function unpack($data, $iVersion, &$outParams)
    {
        $sBuffer     = $data['sBuffer'];
        $definitions = self::getDefineByServant($data['sServantName']);
        $outParams   = $definitions[$data['sFuncName']]['outParams'];
        $returnInfo  = $definitions[$data['sFuncName']]['return'];
        $outParams   = self::getOutParams($outParams, $data['sBuffer'], $iVersion);
        // 还要尝试去获取一下接口的返回码哦
        $returnUnpack = Utils::getUnpackMethods($returnInfo['type']);

        if ($returnInfo['type'] !== 'void') {
            if (Utils::isVector($returnInfo['type']) || Utils::isMap($returnInfo['type'])) {
                $ret = self::getProtocol()->createInstance($returnInfo['proto']);
                return TUPAPIWrapper::$returnUnpack("", $returnInfo['tag'], $ret, $sBuffer, $iVersion);
            } elseif (Utils::isStruct($returnInfo['type'])) {
                $returnVal = new $returnInfo['proto']();
                TUPAPIWrapper::$returnUnpack("", $returnInfo['tag'], $returnVal, $sBuffer, $iVersion);
                return $returnVal;
            } else {
                return TUPAPIWrapper::$returnUnpack("", $returnInfo['tag'], $sBuffer, $iVersion);
            }
        }
        return '';
    }

    public static function getOutParams($outParams, $sBuffer, $iVersion)
    {
        $data = [];
        foreach ($outParams as $param) {
            $type          = $param['type'];
            $valueName     = $param['name'];
            $index         = $param['tag'];
            $unpackMethods = Utils::getUnpackMethods($type);
            $name          = $param['name'];

            if (Utils::isBasicType($type)) {
                $data[$name] = TUPAPIWrapper::$unpackMethods($name, $index, $sBuffer, $iVersion);
            } else {
                // 判断如果是vector需要特别的处理
                if (Utils::isVector($type) || Utils::isMap($type)) {
                    $ret         = self::getProtocol()->createInstance($param['proto']);
                    $data[$name] = TUPAPIWrapper::$unpackMethods($name, $index, $ret, $sBuffer, $iVersion);
                } elseif (Utils::isStruct($type)) {
                    $$name       = new $param['proto']();
                    $ret         = TUPAPIWrapper::$unpackMethods($name, $index, $$name, $sBuffer, $iVersion);
                    $data[$name] = $$name;
                }
            }
        }
        return $data;
    }

    public static function getDefineByInterface($interface)
    {
        if (!isset(TarsDefinition::$definitions[$interface])) {
            throw new \Exception("接口{$interface}的定义信息不存在", 600);
        }

        return TarsDefinition::$definitions[$interface];
    }

    public static function getDefineByServant($servant)
    {
        $definitions = TarsDefinition::$definitions;
        $interfaces  = array_column($definitions, 'methods', 'servant');
        if (!isset($interfaces[$servant])) {
            throw new \Exception("servant {$servant} 的定义信息不存在", 600);
        }
        return $interfaces[$servant];
    }

    public static function getProtocol()
    {
        if (!is_object(self::$protocol)) {
            self::$protocol = new \App\Lib\Tars\Client\TARSProtocol();
        }

        return self::$protocol;
    }
}
