# Swoft Tars Driver Demo

## 中文版

### 简介

Tars driver for Swoft.

Swoft集成微服务治理框架Tars

### 环境依赖

1. PHP 5.4 以上版本
2. Swoole2.0 以上版本
3. Tars

### 实现的特性

* 原生swoft-rpc-server集成tars协议
* 原生swoft-rpc-client集成tars协议
* rpc-server和rpc-client端的写法与原生swoft无任何差异
* 实现了rpc-client调用时，serveruri按servantName和setid的动态获取
* 实现了rpc-client的统计上报

### 计划实现的特性

* ~~服务端异常处理（2018-11-29）~~
* ~~集成tars打包等相关命令到swoft中（2018-11-29）~~
* tars统一的返回结构定义
* 解决引用传参问题
* 服务端keepalive、property上报
* log上报

### 使用

1. 生成server接口文件和client调用文件

    cd src

    php bin/tars tars:tars2php

2. 启动tars-server

    cd src

    composer install

    php index.php

3. 调用测试

    浏览器访问以下地址测试使用tars协议调用的结果

    swoft协程调用
    http://localhost:7999/rpc/tarsClient

    swoft同步调用
    http://localhost:7999/rpc/tarsClient1

    tars-client方式调用
    测试前需要按照tars规范生成tarsclient文件
    http://localhost:7999/rpc/tarsClient2

4. 代码打包发布

    cd src

    php bin/tars tars:deploy
