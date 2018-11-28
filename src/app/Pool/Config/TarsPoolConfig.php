<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Pool\Config;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Value;
use Swoft\Pool\PoolProperties;

/**
 * the config of service tars
 *
 * @Bean()
 */
class TarsPoolConfig extends PoolProperties
{
    /**
     * the name of pool
     *
     * @Value(name="${config.service.tars.name}", env="${TARS_POOL_NAME}")
     * @var string
     */
    protected $name = '';

    /**
     * Minimum active number of connections
     *
     * @Value(name="${config.service.tars.minActive}", env="${TARS_POOL_MIN_ACTIVE}")
     * @var int
     */
    protected $minActive = 5;

    /**
     * the maximum number of active connections
     *
     * @Value(name="${config.service.tars.maxActive}", env="${TARS_POOL_MAX_ACTIVE}")
     * @var int
     */
    protected $maxActive = 50;

    /**
     * the maximum number of wait connections
     *
     * @Value(name="${config.service.tars.maxWait}", env="${TARS_POOL_MAX_WAIT}")
     * @var int
     */
    protected $maxWait = 100;

    /**
     * Maximum waiting time
     *
     * @Value(name="${config.service.tars.maxWaitTime}", env="${TARS_POOL_MAX_WAIT_TIME}")
     * @var int
     */
    protected $maxWaitTime = 3;

    /**
     * Maximum idle time
     *
     * @Value(name="${config.service.tars.maxIdleTime}", env="${TARS_POOL_MAX_IDLE_TIME}")
     * @var int
     */
    protected $maxIdleTime = 60;

    /**
     * the time of connect timeout
     *
     * @Value(name="${config.service.tars.timeout}", env="${TARS_POOL_TIMEOUT}")
     * @var int
     */
    protected $timeout = 200;

    /**
     * the addresses of connection
     *
     * <pre>
     * [
     *  '127.0.0.1:88',
     *  '127.0.0.1:88'
     * ]
     * </pre>
     *
     * @Value(name="${config.service.tars.uri}", env="${TARS_POOL_URI}")
     * @var array
     */
    protected $uri = [];

    /**
     * whether to tars provider(consul/etcd/zookeeper)
     *
     * @Value(name="${config.service.tars.useProvider}", env="${TARS_POOL_USE_PROVIDER}")
     * @var bool
     */
    protected $useProvider = false;

    /**
     * the default balancer is random balancer
     *
     * @Value(name="${config.service.tars.balancer}", env="${TARS_POOL_BALANCER}")
     * @var string
     */
    protected $balancer = '';

    /**
     * the default provider is consul provider
     *
     * @Value(name="${config.service.tars.provider}", env="${TARS_POOL_PROVIDER}")
     * @var string
     */
    protected $provider = '';

    /**
     * @Value(name="${config.service.tars.packer}", env="${TARS_POOL_PACKER}")
     * @var string
     */
    private $packer = "";

    /**
     * @return array
     */
    public function getUri(): array
    {
        if ($uri = \App\Lib\Tars\Client\Helper::getUri()) {
            return $uri;
        }

        return $this->uri;
    }
}
