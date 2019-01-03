<?php

namespace App\Lib\Tars\Common;

use App\Lib\Tars\Common\TARSProtocol;
use Swoft\Bean\Annotation\Definition;
use Swoft\Bean\DefinitionInterface;
use Swoft\Rpc\Client\Bean\Collector\ReferenceCollector;

/**
 * The definition of tars rpc client
 * @Definition()
 */
class TarsDefinition implements DefinitionInterface
{
    public static $definitions;
    public static $servicemap;
    /**
     * array
     */
    public function getDefinitions()
    {
        $tarsdir = dirname(BASE_PATH) . '/tars';
        $tarsmap = [];
        foreach (glob($tarsdir . '/*.php') as $filename) {
            $data = require $filename;
            if (!isset($data['appName'], $data['serverName'], $data['objName'])) {
                continue;
            }
            $data['class']   = $data['appName'] . '\\' . $data['serverName'] . '\\' . $data['objName'];
            $data['servant'] = $data['appName'] . '.' . $data['serverName'] . '.' . $data['objName'];
            $tarsmap[]       = $data;
        }

        if (empty($tarsmap)) {
            return [];
        }

        $collector = ReferenceCollector::getCollector();
        foreach ($collector as $interfaceAry) {
            $interfaceClass = $interfaceAry[1];
            $servant        = null;
            foreach ($tarsmap as $v) {
                if (strpos($interfaceClass, $v['class']) !== false) {
                    $servant = $v['servant'];
                    break;
                }
            }

            if (empty($servant)) {
                continue;
            }

            $interface = new \ReflectionClass($interfaceClass);
            $methods   = $interface->getMethods();
            foreach ($methods as $method) {
                $docblock = $method->getDocComment();
                // 解析注释
                $protocol                  = new TARSProtocol();
                $paramInfos[$method->name] = $protocol->parseAnnotation($docblock);
            }

            self::$definitions[$servant]       = $paramInfos;
            self::$servicemap[$interfaceClass] = $servant;
        }

        return [];
    }
}
