<?php

namespace App\Lib\Tars\Client;

use App\Lib\Tars\Client\QueryFWrapper;
use Tars\monitor\StatFWrapper;

/**
 * PoolHelper
 */
class Helper
{
    /**
     * 上报tars请求结果
     */
    public static function report($servantName, $funcName, $code = 0, $ip = '', $port = '')
    {
        $config = \Tars\Conf::get();
        var_dump($config);
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
            if ($config['tars']['application']['enableset'] == 'Y') {
                $setid = $config['tars']['application']['setdivision'];
            }

            $socketMode = self::getsocketMode();
            $locator    = $config['tars']['application']['client']['locator'];
            $query      = new QueryFWrapper($locator, $socketMode);

            //$servantName = 'PHPTest.PHPServer.obj';
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
