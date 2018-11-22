<?php

namespace App\Lib\Tars\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Rpc\Server\Event\RpcServerEvent;
use Swoft\Rpc\Server\Router\HandlerAdapter;

/**
 * service packer
 *
 * @Bean()
 * @uses      PackerMiddleware
 */
class PackerMiddleware extends \Swoft\Rpc\Server\Middleware\PackerMiddleware
{
    /**
     * packer middleware
     *
     * @param \Psr\Http\Message\ServerRequestInterface     $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $packer = service_packer();
        $data   = $request->getAttribute(self::ATTRIBUTE_DATA);
        $data   = $packer->unpack($data);

        // init data and trigger event
        App::trigger(RpcServerEvent::BEFORE_RECEIVE, null, $data);
        $request = $request->withAttribute(self::ATTRIBUTE_DATA, $data);

        /* @var \Swoft\Rpc\Server\Rpc\Response $response */
        $response      = $handler->handle($request);
        $serviceResult = $response->getAttribute(HandlerAdapter::ATTRIBUTE);

        $serviceResult['requestdata'] = $data;
        $serviceResult                = $packer->pack($serviceResult);

        return $response->withAttribute(HandlerAdapter::ATTRIBUTE, $serviceResult);
    }
}
