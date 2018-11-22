<?php

namespace App\Lib\Tars\Client;

use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Core\RequestContext;
use Swoft\Rpc\Packer\PackerInterface;

/**
 * Class TarsPacker
 * @Bean()
 */
class TarsPacker implements PackerInterface
{
    /**
     * pack data
     *
     * @param mixed $data
     * @return string
     * @throws \InvalidArgumentException
     */
    public function pack($data): string
    {
        RequestContext::setContextDataByKey('requestparams', $data['params']);
        $config      = App::$properties['tars'];
        $clientClass = str_replace($config['ServerNamespacePrefix'], $config['clientNamespacePrefix'], $data['interface']);
        $config      = new \Tars\client\CommunicatorConfig();
        $servant     = new $clientClass($config, true);
        if (empty(\config($servant->_servantName))) {
            \Swoft::getBean('config')->set($servant->_servantName, $clientClass);
        }

        $servant->getrequest     = true;
        $servant->getresponse    = false;
        $servant->notinvoke      = true;
        $method                  = $data['method'];
        $requestBuf              = $servant->$method(...$data['params']);
        $requestBuf->_iRequestId = $data['spanid'];
        return $requestBuf->encode();
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
        $decodeRet = \TUPAPI::decode($data);
        if ($decodeRet['iRet'] !== 0) {
            $msg = isset($decodeRet['sResultDesc']) ? $decodeRet['sResultDesc'] : "";
            return [
                'status' => 600,
                'msg'    => $msg,
                'data'   => '',
            ];
        }

        $clientClass = \config($decodeRet['sServantName']);
        if (empty($clientClass)) {
            return ['status' => 600, 'msg' => '返回的数据包有误', 'data' => ''];
        }
        $config                  = new \Tars\client\CommunicatorConfig();
        $servant                 = new $clientClass($config, true);
        $servant->getrequest     = false;
        $servant->getresponse    = true;
        $servant->notinvoke      = true;
        $servant->responseBuffer = $decodeRet['sBuffer'];
        $method                  = $decodeRet['sFuncName'];
        $params                  = RequestContext::getContextDataByKey('requestparams');
        $returnVal               = $servant->$method(...$params);
        return [
            'status' => 200,
            'msg'    => '',
            'data'   => $returnVal ?: '',
        ];
    }
}
