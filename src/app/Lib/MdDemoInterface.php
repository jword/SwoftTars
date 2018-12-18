<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Lib;

/**
 * The middleware interface service
 */
interface MdDemoInterface
{
    /**
     * @return array
     */
    public function parentMiddleware();

    /**
     * @return array
     */
    public function funcMiddleware();
}
