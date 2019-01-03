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
use App\Servant\SwoftDemo\Demo\TarsDemo\classes\OutStruct;
use App\Servant\SwoftDemo\Demo\TarsDemo\classes\SimpleStruct;
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
     * @Reference(name="tars")
     *
     * @var App\Servant\SwoftDemo\Demo\TarsDemo\TarsDemoServiceServant
     */
    private $tarsService;

    /**
     * @Reference(name="tars")
     *
     * @var App\Servant\Swoft\Demo\Demo\TarsDemoServiceServant
     */
    private $tarsService2;

    /**
     * swoft-rpc-tars-client协程调用测试
     */
    public function tarsClient()
    {
        $name      = 'ted';
        $greetings = 44;
        //测试struct return
        $result1 = $this->tarsService->defertestReturn();
        //测试map return
        $result2 = $this->tarsService->defertestReturn2();
        //测试void
        $result3 = $this->tarsService->defersayHelloWorld($name, $greetings);
        //测试struct
        $c       = new OutStruct;
        $d       = new SimpleStruct;
        $result4 = $this->tarsService->defertestStruct(65, $d, $c);
        //测试basic
        $result5 = $this->tarsService->defertestBasic(true, 6, 'ddddd');
        //测试map
        $b       = new SimpleStruct;
        $m1      = new \TARS_Map(\TARS::STRING, \TARS::STRING);
        $result6 = $this->tarsService->defertestMap(6, $d, $m1);

        //vector
        $v1                   = new \TARS_Vector(\TARS::STRING);
        $simpleStruct1        = new SimpleStruct();
        $simpleStruct1->id    = 1;
        $simpleStruct1->count = 2;
        $simpleStruct2        = new SimpleStruct();
        $simpleStruct2->id    = 2;
        $simpleStruct2->count = 4;
        $v2                   = [$simpleStruct1, $simpleStruct2];
        $result7              = $this->tarsService->defertestVector(65, $v1, $v2);
        $result1              = $result1->getResult();
        $result2              = $result2->getResult();
        $result3              = $result3->getResult();
        $result4              = $result4->getResult();
        $result5              = $result5->getResult();
        $result6              = $result6->getResult();
        $result7              = $result7->getResult();

        return compact('result1', 'result2', 'result3', 'result4', 'result5', 'result6', 'result7');
    }

    /**
     *  swoft-rpc-tars-client同步调用测试
     */
    public function tarsClient1()
    {
        $name      = 'ted';
        $greetings = 44;
        $c         = new OutStruct;
        $d         = new SimpleStruct;
        $result1   = $this->tarsService->testStruct(65, $d, $c);
        $result1   = $this->tarsService->testReturn();
        $result2   = $this->tarsService->testReturn2();
        $result3   = $this->tarsService->sayHelloWorld($name, $greetings);
        return compact('result1', 'result2', 'result3', 'greetings');
    }

    /**
     * 原生client调用测试
     */
    public function tarsClient2()
    {
        $v3 = new \TARS_Vector(\TARS::INT32);
        $v3->pushBack(intval(2));
        $v3->pushBack(intval(2));

        $routeInfo = [
            ['sIp' => '127.0.0.1', 'iPort' => 8099],
        ];
        $config               = new \Tars\client\CommunicatorConfig();
        $v1                   = ['aaa'];
        $simpleStruct1        = new SimpleStruct();
        $simpleStruct1->id    = 1;
        $simpleStruct1->count = 2;
        $simpleStruct2        = new SimpleStruct();
        $simpleStruct2->id    = 2;
        $simpleStruct2->count = 4;
        $v2                   = [$simpleStruct1, $simpleStruct2];

        //$config->setLocator("tars.tarsregistry.QueryObj@tcp -h 192.168.8.58 -p 17890");
        //$config->init(BASE_PATH . '/conf/test.config.conf');
        $config->setRouteInfo($routeInfo);
        $config->setSocketMode(3); //1标识socket 2标识swoole同步 3标识swoole协程
        $config->setModuleName('SwoftDemo.Demo.TarsDemo');
        $config->setCharsetName('UTF-8');
        $servant = new \App\TarsClient\SwoftDemo\Demo\TarsDemo\TarsDemoServiceServant($config);
        echo "Service ip and port specified with socket mode 2 (swoole client)\n";

        $result1 = $servant->testReturn();
        $result2 = $servant->testReturn2();
        $result3 = $servant->testVector(65, $v1, $v2, $v3, $v4);

        return compact('result1', 'result2', 'result3');
    }

    /**
     *  swoft-rpc-tars-client多servant调用测试
     */
    public function tarsClient3()
    {
        $name      = 'ted';
        $greetings = 44;
        $c         = new OutStruct;
        $d         = new SimpleStruct;
        $result1   = $this->tarsService->testStruct(65, $d, $c);
        $result1   = $this->tarsService2->testReturn();
        $result2   = $this->tarsService->testReturn2();
        $result3   = $this->tarsService2->sayHelloWorld($name, $greetings);
        return compact('result1', 'result2', 'result3', 'greetings');
    }
}
