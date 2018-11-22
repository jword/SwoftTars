<?php
$cmd = isset($argv[2]) ? strtolower($argv[2]) : 'start';
//获取服务配置信息获取目录
/*if (isset($argv[1])) {
require_once __DIR__ . '/vendor/autoload.php';
$configPath = $argv[1];
$pos        = strpos($configPath, '--config=');
$configPath = substr($configPath, $pos + 9);
//对配置文件进行处理
$tarsConfig = \Tars\Utils::parseFile($configPath);

$basePath = $tarsConfig['tars']['application']['server']['basepath'];

$servicesInfo = require $basePath . 'src/services.php';
//读取到配置后的前置处理
$table = \Tars\Conf::getInstance();
//将tars-server配置与本地server配置合并，并将table赋值到server上
//待处理

//解析配置，如果servType = tcp
if ($tarsConfig['tars']['application']['server']['servType'] === 'tcp') {
$cmd = 'rpc:' . $argv[2];
}

//监控
$monitorStoreClassName = isset($servicesInfo['monitorStoreConf']['className']) ? $servicesInfo['monitorStoreConf']['className'] : \Tars\monitor\cache\SwooleTableStoreCache::class;
$monitorStoreConfig    = isset($servicesInfo['monitorStoreConf']['config']) ? $servicesInfo['monitorStoreConf']['config'] : [];
$storeCache            = new $monitorStoreClassName($monitorStoreConfig);
\Tars\monitor\StatFWrapper::initStoreCache($storeCache);
}*/

//处理server启动参数，启动server
$argv[1] = $_SERVER['argv'][1] = $cmd;
$argv[0] = $_SERVER['argv'][0] = __DIR__ . '/bin/swoft';
if ($cmd == 'start') {
    //$argv[] = $_SERVER['argv'][] = '-d';
}

!defined('RPC_PROTOCOL') && define('RPC_PROTOCOL', 'tars');

require_once __DIR__ . '/bin/swoft';
