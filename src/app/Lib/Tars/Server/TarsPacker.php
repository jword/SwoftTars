<?php

namespace App\Lib\Tars\Server;

use App\Lib\Tars\Common\Helper;
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
    protected $protocol;

    /**
     * pack data
     *
     * @param mixed $data
     * @return string
     */
    public function pack($data): string
    {
        if ($data['status'] != 200) {
            //异常的情况
            $unpackResult               = RequestContext::getContextDataByKey('unpackResult');
            $unpackResult['iVersion']   = 1;
            $unpackResult['iRequestId'] = isset($unpackResult['iRequestId']) ? $unpackResult['iRequestId'] : 0;
            $rspBuf                     = Helper::getProtocol()->packErrRsp($unpackResult, $data['status'], $data['msg']);
        } else {
            $sFuncName    = $data['requestdata']['method'];
            $args         = $data['requestdata']['params'];
            $unpackResult = $data['requestdata']['unpackResult'];

            $paramInfo = Helper::getDefineByServant($unpackResult['sServantName'], $sFuncName);

            $rspBuf = Helper::getProtocol()->packRsp($paramInfo, $unpackResult, $args, $data['data']);
        }

        return $rspBuf;
    }

    /**
     * unpack data
     *
     * @param mixed $data
     * @return mixed
     */
    public function unpack($data)
    {
        $unpackResult  = Helper::getProtocol()->unpackReq($data);
        $sServantName  = $unpackResult['sServantName'];
        $sFuncName     = $unpackResult['sFuncName'];
        $interfaceName = Helper::getInterfaceByServant($sServantName);
        $paramInfo     = Helper::getDefineByServant($sServantName, $sFuncName);

        $args = Helper::getProtocol()->convertToArgs($paramInfo, $unpackResult);

        return [
            'method'       => $sFuncName,
            'version'      => isset($data['version']) ? $data['version'] : 0,
            'interface'    => $interfaceName,
            'params'       => $args,
            'unpackResult' => $unpackResult,
        ];
    }
}
