<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use App\Lib\DemoInterface;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Rpc\Client\Bean\Annotation\Reference;

/**
 * rpc controller test
 *
 * @Controller(prefix="rpc")
 */
class TarsRpcController
{

    /**
     * @Reference(name="user", fallback="demoFallback", packer="json")
     *
     * @var DemoInterface
     */
    private $demoService;

    /**
     * @Reference(name="user", version="1.0.1", packer="json")
     *
     * @var DemoInterface
     */
    private $demoServiceV2;

    /**
     * @Reference(name="user", packer="json")
     * @var \App\Lib\MdDemoInterface
     */
    private $mdDemoService;

    /**
     * @Inject()
     * @var \App\Models\Logic\UserLogic
     */
    private $logic;

    /**
     * @Reference(name="tars", packer="tarsclient")
     *
     * @var App\Lib\SwoftDemo\Demo\TarsDemo\TarsDemoServiceServant
     */
    private $tarsService;

    /**
     * swoft-rpc-tars-client协程调用测试
     */
    public function tarsClient()
    {
        $name      = 'ted';
        $greetings = 44;
        $result1   = $this->tarsService->defertestReturn();
        $result2   = $this->tarsService->defertestReturn2();
        $result3   = $this->tarsService->defersayHelloWorld($name, $greetings);
        $result1   = $result1->getResult();

        var_dump('defertestReturn:');
        var_dump($result1);

        var_dump('defertestReturn2:');
        $result2 = $result2->getResult();
        var_dump($result2);

        var_dump('defersayHelloWorld:');
        $result3 = $result3->getResult();
        var_dump($result3);

        return compact('result1', 'result2', 'result3');

    }

    /**
     *  swoft-rpc-tars-client同步调用测试
     */
    public function tarsClient1()
    {
        $name      = 'ted';
        $greetings = 44;
        $result1   = $this->tarsService->testReturn();
        $result2   = $this->tarsService->testReturn2();
        $result3   = $this->tarsService->sayHelloWorld($name, $greetings);
        return compact('result1', 'result2', 'result3');
    }

    /**
     * 原生client调用测试
     */
    public function tarsClient2()
    {
        $route['sIp']   = '127.0.0.1';
        $route['iPort'] = 8099;
        $routeInfo[]    = $route;
        $config         = new \Tars\client\CommunicatorConfig();
        $config->setRouteInfo($routeInfo);
        $config->setSocketMode(3); //1标识socket 2标识swoole同步 3标识swoole协程
        $config->setModuleName('SwoftDemo.Demo.TarsDemo');
        $config->setCharsetName('UTF-8');
        $servant = new \App\TarsClient\SwoftDemo\Demo\TarsDemo\TarsDemoServiceServant($config);
        echo "Service ip and port specified with socket mode 2 (swoole client)\n";

        //成功
        $result1 = $servant->testReturn();
        var_dump('testReturn:');
        var_dump($result1);

        $c = $servant->testReturn2();
        var_dump('testReturn2:');
        var_dump($c);

        $name      = 'ted';
        $greetings = 44;
        $result    = $servant->sayHelloWorld($name, $greetings);
        var_dump('sayHelloWorld:');
        var_dump($result);
        var_dump($greetings);

        return compact('result1', 'c', 'name', 'greetings');
    }
}
