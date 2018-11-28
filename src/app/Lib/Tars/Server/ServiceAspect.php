<?php
namespace App\Lib\Tars\Server;

use Swoft\Aop\JoinPoint;
use Swoft\Bean\Annotation\AfterReturning;
use Swoft\Bean\Annotation\Aspect;
use Swoft\Bean\Annotation\Before;
use Swoft\Bean\Annotation\PointBean;
use Swoft\Core\RequestContext;
use Swoft\Rpc\Server\Bean\Annotation\Service;

/**
 * service aspcet
 *
 * @Aspect()
 * @PointBean(
 *     include={
 *         App\Services\TarsDemoService::class
 *     },
 * )
 */
class ServiceAspect
{

    /**
     * @Before()
     */
    public function before()
    {

    }

    /**
     * @AfterReturning()
     */
    public function afterReturn(JoinPoint $joinPoint)
    {
        $result = $joinPoint->getReturn();
        $args   = $joinPoint->getArgs();
        RequestContext::setContextDataByKey('returnargs', $args);
        return $result;
    }
}
