<?php

namespace App\Lib\Tars\Client;

use Tars\registry\RouteTable;

class QueryFWrapper extends \Tars\registry\QueryFWrapper
{
    public function findObjectByIdInSameSet($id, $setId, &$activeEp, &$inactiveEp)
    {
        try {
            $servantName = $id;
            $id          = $id . '_' . $setId;
            if (class_exists('swoole_table') && php_sapi_name() !== "apache" && php_sapi_name() !== "fpm-fcgi") {
                RouteTable::getInstance();
                $result    = RouteTable::getRouteInfo($id);
                $routeInfo = $result['routeInfo'];

                if (!empty($routeInfo)) {
                    $timestamp = $result['timestamp'];
                    if (time() - $timestamp < $this->_refreshInterval / 1000) {
                        return $routeInfo;
                    }
                }
            }

            $this->_queryF->findObjectByIdInSameSet($servantName, $setId, $activeEp, $inactiveEp);
            $routeInfo = [];
            foreach ($activeEp as $endpoint) {
                $route['sIp']     = $endpoint['host'];
                $route['iPort']   = $endpoint['port'];
                $route['timeout'] = $endpoint['timeout'];
                $route['bTcp']    = $endpoint['istcp'];
                $routeInfo[]      = $route;
            }

            // 这里你能起一个定时器么,i think not, 但是可以起swooletable
            // 然后在server里面轮询,再去刷swooletable里面缓存的数据
            if (class_exists('swoole_table') && php_sapi_name() !== "apache" && php_sapi_name() !== "fpm-fcgi") {
                RouteTable::getInstance();
                RouteTable::setRouteInfo($id, $routeInfo);
            }

            return $routeInfo;
        } catch (\Exception $e) {
            // 发生异常之后,需要对主控进行兜底
            if (class_exists('swoole_table') && php_sapi_name() !== "apache" && php_sapi_name() !== "fpm-fcgi") {
                RouteTable::getInstance();
                $result    = RouteTable::getRouteInfo($id);
                $routeInfo = $result['routeInfo'];

                return $routeInfo;
            }

            throw $e;
        }
    }
}
