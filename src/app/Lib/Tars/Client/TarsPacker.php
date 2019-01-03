<?php

namespace App\Lib\Tars\Client;

use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Core\RequestContext;
use Swoft\Rpc\Packer\PackerInterface;
use Tars\client\RequestPacket;

/**
 * Class TarsPacker
 * @Bean()
 */
class TarsPacker implements PackerInterface
{
    private $iVersion = 3;
    /**
     * pack data
     *
     * @param mixed $data
     * @return string
     * @throws \InvalidArgumentException
     */
    public function pack($data): string
    {
        $requestPacket               = new RequestPacket();
        $requestPacket->_iVersion    = $this->iVersion;
        $requestPacket->_funcName    = $data['method'];
        $requestPacket->_servantName = TarsHelper::getServantByInterface($data['interface']);
        $requestPacket->_encodeBufs  = TarsHelper::pack($data, $this->iVersion);

        if ($this->iVersion == 1) {
            $reqs         = RequestContext::getContextDataByKey('reqs');
            $reqs         = is_array($reqs) ? $reqs : [];
            $reqid        = count($reqs);
            $reqs[$reqid] = ['servantName' => $requestPacket->_servantName, 'method' => $data['method']];
            RequestContext::setContextDataByKey('reqs', $reqs);
        }

        $requestPacket->_iRequestId = isset($reqid) ? $reqid : $data['spanid'];
        return $requestPacket->encode();
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
        $decodeRet = \TUPAPI::decode($data, $this->iVersion);
        if (isset($decodeRet['sServantName']) && empty($decodeRet['sServantName'])) {
            $decodeRet = \TUPAPI::decode($data, 1);
        }

        if ($decodeRet['iRet'] !== 0) {
            if (APP_ENV == 'dev') {
                throw new \Exception($decodeRet['sResultDesc'], $decodeRet['iRet']);
            }

            $msg = isset($decodeRet['sResultDesc']) ? $decodeRet['sResultDesc'] : "";
            return [
                'status' => $decodeRet['iRet'],
                'msg'    => $msg,
                'data'   => '',
            ];
        }

        if ($this->iVersion == 1) {
            $data                      = RequestContext::getContextDataByKey('reqs');
            $decodeRet['sServantName'] = $data[$decodeRet['iRequestId']]['servantName'];
            $decodeRet['sFuncName']    = $data[$decodeRet['iRequestId']]['method'];
        }

        $returnVal = TarsHelper::unpack($decodeRet, $this->iVersion, $outParams);

        return [
            'status' => 200,
            'msg'    => '',
            'data'   => $returnVal ?: '',
        ];
    }
}
