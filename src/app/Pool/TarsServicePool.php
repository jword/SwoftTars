<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Pool;

use App\Pool\Config\TarsPoolConfig;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Pool;
use Swoft\Rpc\Client\Pool\ServicePool;

/**
 * the pool of tars service
 *
 * @Pool(name="tars")
 */
class TarsServicePool extends ServicePool
{
    /**
     * @Inject()
     *
     * @var TarsPoolConfig
     */
    protected $poolConfig;
}
