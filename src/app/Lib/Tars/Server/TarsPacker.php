<?php

namespace App\Lib\Tars\Server;

use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Rpc\Packer\PackerInterface;
use Swoft\Rpc\Server\Bean\Collector\ServiceCollector;
use Tars\Code;

/**
 * Class TarsPacker
 * @Bean()
 */
class TarsPacker implements PackerInterface
{
    protected $protocol;
    /**
     * pack data
     *
     * @param mixed $data
     * @return string
     * @throws \InvalidArgumentException
     */
    public function pack($data): string
    {
        $sFuncName    = $data['requestdata']['method'];
        $args         = $data['requestdata']['params'];
        $unpackResult = $data['requestdata']['unpackResult'];

        //优化
        $paramInfos = [];
        $interface  = new \ReflectionClass($data['requestdata']['interface']);
        $methods    = $interface->getMethods();
        foreach ($methods as $method) {
            $docblock = $method->getDocComment();
            // 对于注释也应该有自己的定义和解析的方式
            $paramInfos[$method->name] = $this->getProtocol()->parseAnnotation($docblock);
        }

        if (!isset($paramInfos[$sFuncName])) {
            return '';
            //throw new \Exception(Code::TARSSERVERUNKNOWNERR);
        }

        $paramInfo = $paramInfos[$sFuncName];
        $rspBuf    = $this->getProtocol()->packRsp($paramInfo, $unpackResult, $args, $data['data']);
        return $rspBuf;
    }

    /**
     * unpack data
     *
     * @param mixed $data
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function unpack($data)
    {
        $unpackResult  = $this->getProtocol()->unpackReq($data);
        $sServantName  = $unpackResult['sServantName'];
        $sFuncName     = $unpackResult['sFuncName'];
        $interfaceName = $this->getInterface($sServantName);

        //优化
        $paramInfos = [];
        $interface  = new \ReflectionClass($interfaceName);
        $methods    = $interface->getMethods();
        foreach ($methods as $method) {
            $docblock = $method->getDocComment();
            // 解析注释
            $paramInfos[$method->name] = $this->getProtocol()->parseAnnotation($docblock);
        }

        if (!isset($paramInfos[$sFuncName])) {
            throw new \Exception(Code::TARSSERVERUNKNOWNERR);
        }

        $paramInfo = $paramInfos[$sFuncName];

        $args = $this->getProtocol()->convertToArgs($paramInfo, $unpackResult);

        return [
            'method'       => $sFuncName,
            'version'      => isset($data['version']) ? $data['version'] : 0,
            'interface'    => $interfaceName,
            'params'       => $args,
            //'args'         => $args,
            'unpackResult' => $unpackResult,
            //'sFuncName'    => $sFuncName,
        ];
    }

    private function getProtocol()
    {
        if (!is_object($this->protocol)) {
            $this->protocol = new \Tars\protocol\TARSProtocol();
        }

        return $this->protocol;
    }

    /**
     * 根据sServantName获取接口名称
     * @return $interface
     */
    private function getInterface($servantName)
    {
        $preg           = str_replace('.', '\\', $servantName);
        $interface      = '';
        $serviceMapping = ServiceCollector::getCollector();
        foreach ($serviceMapping as $k => $v) {
            if (strpos($k, $preg) !== false) {
                $interface = $k;
                break;
            }
        }
        return $interface;
    }
}
