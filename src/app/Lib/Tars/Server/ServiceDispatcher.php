<?php

namespace App\Lib\Tars\Server;

use App\Lib\Tars\Server\PackerMiddleware;
use Swoft\Rpc\Server\Middleware\RouterMiddleware;

/**
 * Service dispatcher
 */
class ServiceDispatcher extends \Swoft\Rpc\Server\ServiceDispatcher
{
    /**
     * Pre middleware
     *
     * @return array
     */
    public function preMiddleware(): array
    {
        return [
            PackerMiddleware::class,
            RouterMiddleware::class,
        ];
    }
}
