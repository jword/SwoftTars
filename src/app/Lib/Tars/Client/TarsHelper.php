<?php

namespace App\Lib\Tars\Client;

use App\Lib\Tars\Common\Helper;
use Tars\client\TUPAPIWrapper;

/**
 * TarsHelper
 */
class TarsHelper
{
    /**
     * 打包请求数据
     */
    public static function pack($data, $iVersion)
    {
        $definitions = Helper::getDefineByInterface($data['interface'], $data['method']);
        $inParams    = $definitions['inParams'];
        $encodeBufs  = [];
        foreach ($inParams as $param) {
            $type = $param['type'];

            $packMethod = Utils::getPackMethods($type);
            $valueName  = $param['name'];
            $index      = $param['tag'];
            $paramvalue = $data['params'][$index - 1];
            // 判断如果是vector需要特别的处理
            if (Utils::isVector($type)) {
                $vec = Helper::getProtocol()->createInstance($param['proto']);
                foreach ($paramvalue as $value) {
                    $vec->pushBack($value);
                }
                $buffer = TUPAPIWrapper::$packMethod($valueName, $index, $vec, $iVersion);
            } elseif (Utils::isMap($type)) {
                $map = Helper::getProtocol()->createInstance($param['proto']);
                foreach ($paramvalue as $key => $value) {
                    $map->pushBack([$key => $value]);
                }
                $buffer = TUPAPIWrapper::$packMethod($valueName, $index, $map, $iVersion);
            } else {
                $buffer = TUPAPIWrapper::$packMethod($valueName, $index, $paramvalue, $iVersion);
            }
            $encodeBufs[$valueName] = $buffer;
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
        $definitions = Helper::getDefineByServant($data['sServantName'], $data['sFuncName']);
        $returnInfo  = $definitions['return'];
        $outParams   = self::getOutParams($definitions['outParams'], $data['sBuffer'], $iVersion);
        // 还要尝试去获取一下接口的返回码哦
        $returnUnpack = Utils::getUnpackMethods($returnInfo['type']);

        if ($returnInfo['type'] !== 'void') {
            if (Utils::isVector($returnInfo['type']) || Utils::isMap($returnInfo['type'])) {
                $ret = Helper::getProtocol()->createInstance($returnInfo['proto']);
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
                    $ret         = Helper::getProtocol()->createInstance($param['proto']);
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

    public static function getServantByInterface($interface)
    {
        return Helper::getServantByInterface($interface);
    }

    /**
     * 上报tars请求结果
     */
    public static function report($interface, $funcName, $code = 0, $ip = '', $port = '')
    {
        $config = \Tars\Conf::get();
        if (empty($config)) {
            return true;
        }
        $servantName    = \App\Lib\Tars\Common\Helper::getServantByInterface($interface);
        $locator        = $config['tars']['application']['client']['locator'];
        $socketMode     = self::getsocketMode();
        $startTime      = \Swoft\Core\RequestContext::getContextDataByKey('requestTime');
        $reportInterval = empty($config['tars']['application']['client']['report-interval']) ? 60000 : $config['tars']['application']['client']['report-interval'];
        $statf          = new StatFWrapper($locator, $socketMode,
            $config['tars']['application']['client']['stat'], $servantName, $reportInterval);
        $runtime = self::militime((microtime(true) - $startTime));
        $statf->addStat($servantName, $funcName, $ip, $port, $runtime, $code, $code);
        return true;
    }

    public static function getUri()
    {
        $servantName = \Swoft\Core\RequestContext::getContextDataByKey('servantName');
        if ($servantName) {
            //从配置中读取
            $config = \Tars\Conf::get();
            if (empty($config)) {
                return null;
            }
            if ($config['tars']['application']['enableset'] == 'Y') {
                $setid = $config['tars']['application']['setdivision'];
            }

            $socketMode = self::getsocketMode();
            $locator    = $config['tars']['application']['client']['locator'];
            $query      = new QueryFWrapper($locator, $socketMode);

            if (!empty($setid)) {
                $activeEp = $inactiveEp = null;
                $routes   = $query->findObjectByIdInSameSet($servantName, $setid, $activeEp, $inactiveEp);
            } else {
                $routes = $query->findObjectById($servantName);
            }

            if (!empty($routes)) {
                $uri = [];
                foreach ($routes as $v) {
                    $uri[] = $v['sIp'] . ':' . $v['iPort'];
                }
                return $uri;
            }
        }
        return null;
    }

    public static function getsocketMode()
    {
        if (\Swoft\App::isCoContext()) {
            $socketMode = 3;
        } else {
            $socketMode = 2;
        }
        return $socketMode;
    }

    public static function militime($microtime = null)
    {
        if (empty($microtime)) {
            $microtime = microtime(true);
        }
        $miliseconds = (float) sprintf('%.0f', $microtime * 1000);

        return $miliseconds;
    }
}
