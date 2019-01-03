<?php
namespace App\Services;

use App\Servant\Swoft\Demo\Demo\classes\ComplicatedStruct;
use App\Servant\Swoft\Demo\Demo\classes\LotofTags;
use App\Servant\Swoft\Demo\Demo\classes\OutStruct;
use App\Servant\Swoft\Demo\Demo\classes\SimpleStruct;
use App\Servant\Swoft\Demo\Demo\TarsDemoServiceServant;
use Conf\ENVConf;
//use Server\cservant\Test\TarsStressServer\StressObj\StressServant;
use Swoft\Core\ResultInterface;
use Swoft\Rpc\Server\Bean\Annotation\Service;
use Tars\client\CommunicatorConfig;
use Tars\monitor\PropertyFWrapper;

/**
 * test servcie
 *
 * @method ResultInterface testReturn()
 * @Service()
 */
class TarsDemoService2 implements TarsDemoServiceServant
{
    public function testTafServer()
    {
        error_log('testTafServer test', 3, '/data/logs/ted.log');
    }
    /**
     * @param struct $tags    \Server\servant\PHPTest\PHPServer\obj\classes\LotofTags
     * @param struct $outtags \Server\servant\PHPTest\PHPServer\obj\classes\LotofTags =out=
     *
     * @return int
     */
    public function testLofofTags(LotofTags $tags, LotofTags &$outtags)
    {
        $outtags->count = 100;

        return 999;
    }
    /**
     * @param string $name
     * @param string $outGreetings =out=
     */
    public function sayHelloWorld($name, &$outGreetings)
    {
        $outGreetings = 'hello world!';
        return $outGreetings;
    }
    /**
     * @param bool   $a
     * @param int    $b
     * @param string $c
     * @param bool   $d =out=
     * @param int    $e =out=
     * @param string $f =out=
     *
     * @return int
     */
    public function testBasic($a, $b, $c, &$d, &$e, &$f)
    {
        $d = false;
        $e = 111;
        $f = 'here i am';

        return -99;
    }
    /**
     * @param long   $a
     * @param struct $b \Server\servant\PHPTest\PHPServer\obj\classes\SimpleStruct
     * @param struct $d \Server\servant\PHPTest\PHPServer\obj\classes\OutStruct =out=
     *
     * @return string
     */
    public function testStruct($a, SimpleStruct $b, OutStruct &$d)
    {
        //error_log('testStruct a:' . $a, 3, '/data/logs/ted.log');
        //error_log('testStruct b:' . var_export($b, true), 3, '/data/logs/ted.log');

        //$d->id = 10000;
        $b->id = 999;

        return 'ok';
    }
    /**
     * @param short  $a
     * @param struct $b  \Server\servant\PHPTest\PHPServer\obj\classes\SimpleStruct
     * @param map    $m1 \TARS_Map(\TARS::STRING,\TARS::STRING)
     * @param struct $d  \Server\servant\PHPTest\PHPServer\obj\classes\OutStruct =out=
     * @param map    $m2 \TARS_Map(\TARS::INT32,\Server\servant\PHPTest\PHPServer\obj\classes\SimpleStruct) =out=
     *
     * @return string
     */
    public function testMap($a, SimpleStruct $b, $m1, OutStruct &$d, &$m2)
    {
        $d->page          = 1024;
        $simpleStruct     = new SimpleStruct();
        $simpleStruct->id = 888;

        $m2->push_back([1 => $simpleStruct]);

        return 'success';
    }
    /**
     * @param int    $a
     * @param vector $v1 \TARS_Vector(\TARS::STRING)
     * @param vector $v2 \TARS_Vector(\Server\servant\PHPTest\PHPServer\obj\classes\SimpleStruct)
     * @param vector $v3 \TARS_Vector(\TARS::INT32) =out=
     * @param vector $v4 \TARS_Vector(\Server\servant\PHPTest\PHPServer\obj\classes\OutStruct) =out=
     *
     * @return string
     */
    public function testVector($a, $v1, $v2, &$v3, &$v4)
    {
        /*$v3->pushBack(111);
        $v3->pushBack(222);*/

        $outStruct     = new OutStruct();
        $outStruct->id = 888;
        $v4->pushBack($outStruct);
        $v4->pushBack($outStruct);
        return 'not very happy';
    }
    /**
     * @return struct \Server\servant\PHPTest\PHPServer\obj\classes\SimpleStruct
     */
    public function testReturn()
    {
        $simpleStruct     = new SimpleStruct();
        $simpleStruct->id = 999999;

        return $simpleStruct;
    }
    /**
     * @return map \TARS_Map(\TARS::STRING,\TARS::STRING)
     */
    public function testReturn2()
    {
        $map = new \TARS_Map(\TARS::STRING, \TARS::STRING);
        $map->pushBack(['this is map test1', 'this is map test2']);

        return $map;
    }
    /**
     * @param struct $cs   \Server\servant\PHPTest\PHPServer\obj\classes\ComplicatedStruct
     * @param vector $vcs  \TARS_Vector(\Server\servant\PHPTest\PHPServer\obj\classes\ComplicatedStruct)
     * @param struct $ocs  \Server\servant\PHPTest\PHPServer\obj\classes\ComplicatedStruct =out=
     * @param vector $ovcs \TARS_Vector(\Server\servant\PHPTest\PHPServer\obj\classes\ComplicatedStruct) =out=
     *
     * @return vector \TARS_Vector(\Server\servant\PHPTest\PHPServer\obj\classes\SimpleStruct)
     */
    public function testComplicatedStruct(ComplicatedStruct $cs, $vcs, ComplicatedStruct &$ocs, &$ovcs)
    {
        $ocs      = new ComplicatedStruct();
        $ocs->str = 'sss';
        $sim      = new SimpleStruct();
        $sim->id  = 888;
        $ocs->ss->pushBack($sim);

        $ovcs->pushBack($ocs);

        $result           = new \TARS_Vector(new SimpleStruct());
        $simpleStruct     = new SimpleStruct();
        $simpleStruct->id = 888;
        $result->pushBack($simpleStruct);

        return $result;
    }
    /**
     * @param map $mcs  \TARS_Map(\TARS::STRING,\Server\servant\PHPTest\PHPServer\obj\classes\ComplicatedStruct)
     * @param map $omcs \TARS_Map(\TARS::STRING,\Server\servant\PHPTest\PHPServer\obj\classes\ComplicatedStruct) =out=
     *
     * @return map \TARS_Map(\TARS::STRING,\Server\servant\PHPTest\PHPServer\obj\classes\ComplicatedStruct)
     */
    public function testComplicatedMap($mcs, &$omcs)
    {
        $com      = new ComplicatedStruct();
        $com->str = 'sss';
        $sim      = new SimpleStruct();
        $sim->id  = 888;
        $com->ss->pushBack($sim);

        $omcs->pushBack(['key' => $com]);

        return $omcs;
    }
    /**
     * @param short  $a
     * @param bool   $b1  =out=
     * @param int    $in2 =out=
     * @param struct $d   \Server\servant\PHPTest\PHPServer\obj\classes\OutStruct =out=
     * @param vector $v3  \TARS_Vector(\Server\servant\PHPTest\PHPServer\obj\classes\OutStruct) =out=
     * @param vector $v4  \TARS_Vector(\TARS::INT32) =out=
     *
     * @return int
     */
    public function testEmpty($a, &$b1, &$in2, OutStruct &$d, &$v3, &$v4)
    {
        return -99;
    }
    /**
     * @return int
     */
    public function testSelf()
    {
        // 这里请求一个其他的taf
        $config = new CommunicatorConfig();
        $config->setLocator(ENVConf::$locator);
        $config->setModuleName('PHPTest.PHPServer');
        $config->setSocketMode(2);

        //$cServant = new StressServant($config);

        // $value = $cServant->testStr('in', $out);

        //error_log('111 and ' . $value . ' out:' . $out);

        return 999;
    }

    /**
     * @return int
     */
    public function testProperty()
    {
        $property = new PropertyFWrapper(ENVConf::$locator, 2, 'PHPTest.PHPServer');
        $property->monitorProperty('127.0.0.1', 'test', 'MAX', 1);
        $property->monitorProperty('127.0.0.1', 'test', 'COUNT', 1);
        $property->monitorProperty('127.0.0.1', 'test', 'MIN', 3);
    }
}
