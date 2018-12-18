<?php

namespace App\Lib\Tars\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Bean\Annotation\Bean;
use Swoft\Core\RequestContext;
use Swoft\Http\Message\Middleware\MiddlewareInterface;
use Swoft\Rpc\Server\Middleware\PackerMiddleware;
use Swoft\Rpc\Server\Router\HandlerAdapter;

/**
 * @Bean()
 * @uses      RouterMiddleware
 * @version   2018年11月23日
 * @author    likunlun <likunlun@gongchang.com>
 * @copyright Copyright 2010-2018 gongchang.com
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class ResultDealMiddleware implements MiddlewareInterface
{

    /**
     * 结果处理中间件
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getAttribute(PackerMiddleware::ATTRIBUTE_DATA);
        RequestContext::setContextDataByKey('unpackResult', $data['unpackResult']);
        $response      = $handler->handle($request);
        $serviceResult = $response->getAttribute(HandlerAdapter::ATTRIBUTE);

        $args = RequestContext::getContextDataByKey('returnargs');
        if (!empty($args)) {
            $data['params'] = $args;
        }

        $serviceResult['requestdata'] = $data;
        return $response->withAttribute(HandlerAdapter::ATTRIBUTE, $serviceResult);
    }
}
