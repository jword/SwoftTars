<?php

namespace Swoft\Rpc\Client;

use App\Lib\Tars\Client\TarsHelper;
use Swoft\App;
use Swoft\Core\ResultInterface;
use Swoft\Helper\JsonHelper;
use Swoft\Helper\PhpHelper;
use Swoft\Pool\ConnectionInterface;
use Swoft\Pool\PoolInterface;
use Swoft\Rpc\Client\Exception\RpcClientException;
use Swoft\Rpc\Client\Service\AbstractServiceConnection;
use Swoft\Rpc\Client\Service\ServiceCoResult;
use Swoft\Rpc\Client\Service\ServiceDataResult;
use Swoft\Sg\Circuit\CircuitBreaker;

/**
 * The service trait
 */
class Service
{
    /**
     * The prefix of defer method
     *
     * @var string
     */
    const DEFER_PREFIX = 'defer';

    /**
     * The name of service
     *
     * @var string``
     */
    protected $name;

    /**
     * @var string
     */
    protected $version;

    /**
     * The name of pool
     *
     * @var string
     */
    protected $poolName;

    /**
     * The name of breaker
     *
     * @var string
     */
    protected $breakerName;

    /**
     * The name of packer
     *
     * @var string
     */
    protected $packerName;

    /**
     * @var string
     */
    protected $interface;

    /**
     * @var string
     */
    protected $fallback;

    /**
     * Do call service
     *
     * @param string $func
     * @param array  $params
     *
     * @throws \Throwable
     * @return mixed
     */
    public function call(string $func, array $params)
    {
        $profileKey  = $this->interface . '->' . $func;
        $fallback    = $this->getFallbackHandler($func);
        $closeStatus = true;

        try {
            $connectPool    = $this->getPool();
            $circuitBreaker = $this->getBreaker();

            /* @var $client AbstractServiceConnection */
            $client = $connectPool->getConnection();
            $type   = $this->getPackerName();
            $packer = service_packer();

            $data     = $packer->formatData($this->interface, $this->version, $func, $params);
            $packData = $packer->pack($data, $type);

            $result = $circuitBreaker->call([$client, 'send'], [$packData], $fallback);
            if ($result === null || $result === false) {
                return null;
            }

            App::profileStart($profileKey);
            try {
                $result = $client->receive();
            } catch (\Throwable $ex) {
                // Client is not connected to server
                if ($ex instanceof RpcClientException && in_array($ex->getCode(), [0, 5001, 104])) {
                    // 0    Send failed, recv data is empty
                    // 104  Connection reset by peer
                    // 5001 SW_ERROR_CLIENT_NO_CONNECTION
                    App::warning(sprintf('%s call %s retried, data=%s, message=%s, code=%s',
                        $this->interface,
                        $func,
                        json_encode($data, JSON_UNESCAPED_UNICODE),
                        $ex->getMessage(),
                        $ex->getCode()
                    ));
                    $client->reconnect();
                    $circuitBreaker->call([$client, 'send'], [$packData], $fallback);
                    $result = $client->receive();
                } else {
                    throw $ex;
                }
            }

            App::profileEnd($profileKey);
            $client->release(true);

            App::debug(sprintf('%s call %s success, data=%s', $this->interface, $func, json_encode($data, JSON_UNESCAPED_UNICODE)));
            //patch
            $result      = $packer->unpack($result, $type);
            $closeStatus = false;
            $data        = $packer->checkData($result);
            //上报结果
            if ($type == 'tarsclient') {
                TarsHelper::report($this->interface, $func, 0);
            }
        } catch (\Throwable $throwable) {
            // If the client is normal, no need to close it.
            if ($closeStatus && isset($client) && $client instanceof AbstractServiceConnection) {
                $client->close();
                App::error(sprintf('%s call %s failed, data=%s, message=%s, code=%s',
                    $this->interface,
                    $func,
                    json_encode($data, JSON_UNESCAPED_UNICODE),
                    $throwable->getMessage(),
                    $throwable->getCode()
                ));
            }
            if (empty($fallback)) {
                throw $throwable;
            }
            $data = PhpHelper::call($fallback, $params);
            //上报异常
            if ($type == 'tarsclient') {
                TarsHelper::report($this->interface, $func, $throwable->getCode());
            }
        }

        return $data;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return ResultInterface
     * @throws RpcClientException
     */
    public function __call(string $name, array $arguments)
    {
        $method = $name;
        $prefix = self::DEFER_PREFIX;
        if (strpos($name, $prefix) !== 0) {
            throw new RpcClientException(sprintf('the method of %s is not exist! ', $name));
        }

        if ($name == $prefix) {
            $method = array_shift($arguments);
        } elseif (strpos($name, $prefix) === 0) {
            $method = lcfirst(ltrim($name, $prefix));
        }

        return $this->deferCall($method, $arguments);
    }

    /**
     * Do call service
     *
     * @param string $func
     * @param array  $params
     *
     * @throws \Throwable
     * @return ResultInterface
     */
    private function deferCall(string $func, array $params)
    {
        $profileKey = $this->interface . '->' . $func;
        $fallback   = $this->getFallbackHandler($func);

        try {
            $connectPool    = $this->getPool();
            $circuitBreaker = $this->getBreaker();

            /* @var ConnectionInterface $connection */
            $connection = $connectPool->getConnection();
            $type       = $this->getPackerName();
            $packer     = service_packer();
            $data       = $packer->formatData($this->interface, $this->version, $func, $params);
            $packData   = $packer->pack($data, $type);

            $result = $circuitBreaker->call([$connection, 'send'], [$packData], $fallback);

            if ($result === null || $result === false) {
                return null;
            }
        } catch (\Throwable $throwable) {
            if (empty($fallback)) {
                throw $throwable;
            }

            $connection = null;
            $result     = PhpHelper::call($fallback, $params);
            if ($type == 'tarsclient') {
                TarsHelper::report($this->interface, $func, $throwable->getCode());
            }
        }

        return $this->getResult($connection, $profileKey, $result);
    }

    /**
     * @param ConnectionInterface $connection
     * @param string              $profileKey
     * @param mixed               $result
     *
     * @return ResultInterface
     */
    private function getResult(ConnectionInterface $connection = null, string $profileKey = '', $result = null)
    {
        if (App::isCoContext()) {
            $serviceCoResult = new ServiceCoResult($result, $connection, $profileKey);
            $serviceCoResult->setFallbackData($result);
            //patch
            $serviceCoResult->setPackerName($this->getPackerName());
            return $serviceCoResult;
        }

        return new ServiceDataResult($result, $connection, $profileKey);
    }

    /**
     * @return CircuitBreaker
     */
    private function getBreaker()
    {
        if (empty($this->breakerName)) {
            return \breaker($this->name);
        }

        return \breaker($this->breakerName);
    }

    /**
     * @return PoolInterface
     */
    private function getPool()
    {
        if (empty($this->poolName)) {
            return App::getPool($this->name);
        }

        return App::getPool($this->poolName);
    }

    /**
     * @return string
     */
    private function getPackerName()
    {
        //patch
        if (empty($this->packerName)) {
            if (isset(App::$properties[$this->poolName]['packer'])) {
                $this->packerName = App::$properties[$this->poolName]['packer'];
            } elseif (isset(App::$properties[$this->name]['packer'])) {
                $this->packerName = App::$properties[$this->name]['packer'];
            }
        }

        return $this->packerName;
    }

    /**
     * @param string $method
     *
     * @return array|null
     */
    private function getFallbackHandler(string $method)
    {
        if (empty($this->fallback)) {
            return null;
        }

        $fallback   = \fallback($this->fallback);
        $interfaces = class_implements(static::class);
        foreach ($interfaces as $interface) {
            if (is_subclass_of($fallback, $interface)) {
                return [$fallback, $method];
            }
        }

        App::warning(sprintf('The %s class does not implement the %s interface', get_parent_class($fallback), JsonHelper::encode($interfaces, JSON_UNESCAPED_UNICODE)));

        return null;
    }
}
