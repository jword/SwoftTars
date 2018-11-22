# Swoft Tars Driver Demo

## 中文版

### 简介

Tars driver for Swoft.

Swoft集成微服务治理框架Tars

### 环境依赖

1. PHP 5.4 以上版本
2. Swoole2.0 以上版本
3. Tars

### 使用

1、生成server接口文件和client调用文件

cd scripts
./tars2php.sh
./tarsclient.php

2、启动tars-server

cd src

php index.php


3、调用测试

浏览器访问以下地址测试使用tars协议调用的结果

swoft协程调用
http://localhost:7999/rpc/tarsClient

swoft同步调用
http://localhost:7999/rpc/tarsClient1

tars-client方式调用
http://localhost:7999/rpc/tarsClient2


