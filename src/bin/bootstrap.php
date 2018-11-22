<?php
//环境变量
if (isset($_ENV['NODE_ENV']) && $_ENV['NODE_ENV']) {
    $env = $_ENV['NODE_ENV'];
} else {
    $env = 'dev';
}

define('APP_ENV', $env);

if ($env == 'prod') {
    ini_set('display_errors', 'Off');
    error_reporting(0);
}

//框架引入
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/define.php';

// 初始化工厂容器
\Swoft\Bean\BeanFactory::init();

/* @var \Swoft\Bootstrap\Boots\Bootable $bootstrap*/
$bootstrap = \Swoft\App::getBean(\Swoft\Bootstrap\Bootstrap::class);
$bootstrap->bootstrap();
