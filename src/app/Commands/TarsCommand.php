<?php
namespace App\Commands;

use Swoft\App;
use Swoft\Console\Bean\Annotation\Command;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Core\Coroutine;
use Tars\deploy\Deploy;

/**
 * tars命令
 *
 * @Command(coroutine=false)
 */
class TarsCommand
{
    /**
     * tars打包命令
     *
     * @param Input  $input
     * @param Output $output
     */
    public function deploy(Input $input, Output $output)
    {
        ini_set('memory_limit', '256M');
        Deploy::run();
    }

    /**
     * tars2php文件生成命令
     * @param Input  $input
     * @param Output $output
     */
    public function tars2php(Input $input, Output $output)
    {
        $commondFile = dirname(BASE_PATH) . '/scripts/tars2php.sh';
        exec("/bin/sh $commondFile");
    }
}
