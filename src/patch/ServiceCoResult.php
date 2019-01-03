<?php

namespace Swoft\Rpc\Client\Service;

use Swoft\App;
use Swoft\Core\AbstractResult;

/**
 * ServiceCoResult
 */
class ServiceCoResult extends AbstractResult
{
    /**
     * @var mixed
     */
    private $fallbackData;

    /**
     * @var mixed
     */
    private $packerName;

    /**
     * @param array ...$params
     *
     * @throws \Throwable
     * @return mixed
     */
    public function getResult(...$params)
    {
        try {
            $result = $this->recv();
            App::debug('service result =' . json_encode($result));
            $packer = service_packer();
            //patch
            $result = $packer->unpack($result, $this->packerName);
            $data   = $packer->checkData($result);
            //上报结果
            if ($this->packerName == 'tarsclient') {
                $profile = explode('->', $this->profileKey);
                \App\Lib\Tars\Client\TarsHelper::report($profile[0], $profile[1], 0);
            }
        } catch (\Throwable $throwable) {
            if (empty($this->fallbackData)) {
                throw $throwable;
            }
            $data = $this->fallbackData;
        }

        return $data;
    }

    /**
     * @param mixed $fallbackData
     */
    public function setFallbackData($fallbackData)
    {
        $this->fallbackData = $fallbackData;
    }

    /**
     * @param mixed $packerName
     */
    public function setPackerName($packerName = '')
    {
        //patch
        $this->packerName = $packerName;
    }
}
