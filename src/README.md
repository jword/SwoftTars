## gc.supseo

### 本地开发环境搭建

    确保本机安装了docker且80端口未被占用

    1、git clone https://git.coding.net/gongchang/gc.supseo.git
    2、cd gc.supseo && composer up
    3、docker-compose up -d （执行前先把gc.supseo设置为docker的共享目录 && 把172.20.18.101:5000加入到insecure registry中）
    4、vi /etc/hosts   将自己想要使用的域名解析到 127.0.0.1     如： 127.0.0.1 posuiji.supseo.net

### swoft学习过程&使用经验


#### 配置文件：

        改造，根据环境变量读取不同配置文件

        容器环境变量如何设置：

            1、从php.ini中读取，此方案需要不同环境使用不同镜像
            2、读取本地环境变量（暂定本方案）
            3、启动容器的时候传参指定容器环境变量


#### server启动流程

        （1） 加载composer autoload文件，引入composer加载的类
        （2） 加载配置定义文件，设置目录别名
        （3） 初始化工厂容器  加载properties下的配置   初始化console.php中的类到server容器中
        （4）依次执行注入的bootstrap文件的bootstrap方法 (Swoft\Bootstrap
        \Boots\InitMbFunsEncoding,Swoft\Bootstrap\Boots\LoadEnv,Swoft
        \Bootstrap\Boots\LoadInitConfiguration")   设置编码、.env环境变
        量、加载config下的server配置
        （5）启动console   command->run()  commandRoute->register
        默认启动 commandServer   可以使用以下命令启动其它server或执行自定义命令

        rpc:xxx    server:xxx    app:xxx  entity:xxx   用户自定义命令：xxx

        命令帮助：
        php /var/www/swoft/bin/swoft -h  查看一级命令列表
        php /var/www/swoft/bin/swoft server -h    查看二级命令列表

        php /var/www/swoft/bin/swoft test:demo 执行自定义命令（自定义命令行程序在 app/commands目录下）

        server:start会调用Swoole\Http\Server->start();
        启动一个同步/异步非阻塞的httpServer服务


        注意onWorkerstart的执行流程：

        每个worker启动前会执行 BeanFactory::reload()方法加载beans和
        properties目录的配置，之后会根据beanScan配置项的配置解析注释，并根据扫
        描到的注解信息执行对应的功能逻辑，并储存在与注解相对应的 Collector 容器
        内，包括但不限于注册路由信息，注册事件监听器，注册中间件，注册过滤器等等


#### client执行流程

    1、request到达后分发到onRequest方法（Swoft\Http\Server\Http\HttpServer）
    2、解析请求参数，格式化
    3、解析响应参数，格式化
    4、分发到对应控制器处理
        （1）回调beforeRequest()
        （2）依次倒序执行以下中间件（系统、view、session、用户、验证）
            循环执行的原理：
            Swoft\Core\RequestHandler
            dispacher调用requestHandler->handle($request)，
            requestHandler将$request和自身的一个克隆对象$this->next，传入第
            一个中间件，第一个中间件执行完毕后，调用克隆对象的handle方法，接着调
            用下一个中间件，直到执行完所有注入的中间件，返回response

            中间件执行流程如下：

            Swoft\Http\Server\Middleware\SwoftMiddleware
            判断请求类型，使用对应解析器（jsonParser）解析request
            \Swoft\Http\Server\Router\HandlerMapping->getHandler($path,$method) 匹配路由
            解析后返回
            request，调用下一个中间件，等待返回

            Swoft\View\Middleware\ViewMiddleware
            判断返回类型是否是view且设置了template,是则将数据渲染到模板中，返回
            带模板内容的的response

            Swoft\Session\Middleware\SessionMiddleware
            处理session，将cookie加入到response中,保存session

            Swoft\Http\Server\Middleware\UserMiddleware
            获取用户注入的中间件，插入到执行流程中来

            Swoft\Http\Server\Middleware\ValidatorMiddleware
            1. 根据注释验证request是否合法，不合法，直接response->end()
            2. 此时中间件执行完毕，requestHandler调用Swoft\Http\Server
            \Router\HandlerAdapter->doHandler解析路由，执行对应
            handler（controller->action()）,执行后，层层返回

        （3）响应请求，释放资源，回调afterRequest()

        通过Swoft\Http\Message\Bean\Collector、
        MiddlewareCollector::collect()或者RequestHandler-
        >insertMiddlewares插入中间件

    中间件带着request信息顺序执行，拿到response后带着response层层返回的好处：
        每一个中间件都能拿到当前层的request信息，当前处理程序的返回值和最终的
        response结果，这样就可以通过注入日志、权限等中间件的方式，将一些业务从逻
        辑层隔离


#### Controller && Service

    controller可以调用logic、data、dao、entity，

    service作为rpc服务的服务层对外提供rpc服务，service实现接口后方可对外提供服务
    一个interface会有多种实现，service通过标识版本号标识接口实现 @Service(version="1.0.1")

    每个service都要定义好interface放在lib目录共服务调用方使用，定义interface的时候需要 使用注解定义类似deferXxx方法，对应service里面方法且首字母大写。这种deferXxx方法，一   般用于业务延迟收包和并发使用.

#### 异常处理

    swoft本身通过try--catch捕获异常和错误（Fatal error无法捕获）
    框架默认通过注解注入app/Exception目录下的SwoftExceptionHandler为异常处理类

#### 注释解析器

    Swoft\xxx\Bean\Wrapper下的对应类保存着相应类注解的解析协议，主要包括
    class、property、method等对应的的解析类及对应类型的解析配置信息（是否解
    析类注解、是否解析方法注解、是否解析属性注解等）， 注释解析成功后会将注解信息注    册到相应的 Collector 容器中，后续代码执行过程中，会调用Collecter的 getCollector（）方法获取储存的内容，并做对应的处理

#### AOP

    swoft通过动态代理模式实现AOP
    要调用的对象的方法会通过代理类动态代理调用

#### 依赖注入：

    Bean
    在config目录下beans目录注入通用的bean

    Inject
    非全局需要的类，需要的时候直接在类注解中通过Inject注入
    @Inject("httpRouter")
    @var \Swoft\Http\Server\Router\HandlerMapping
    private $router;


#### 事件



#### 中间件（middware）、服务提供者（provider）


#### php(trait、自定义协议（wrapper）)

    trait：解决单继承语言代码复用问题

#### 数据库

    如何在一个项目使用多个db?

    1、连接池中不指定数据库，在给实体指定表名的时候加上数据库名
        @Table(name="xx.test")

    2、不同的数据库定义不同的连接池，使用时指定连接池(待实践，目前不清楚如何在实体
    中指定使用哪个连接池)


    连接池的最大活跃连接数
    maxActive，与cpu核数相同时效率最高，再大的话提升效果不明显

#### 任务投递

    Swoft\Task
    task目前有两种类型：

    Task::TYPE_CO（并发执行协程调度task）和 Task::TYPE_ASYNC（异步）

    可以通过 Task::deliver($task, $method,  $params, $type, $timeout)方
    法投递任务

    通过Task::cor(array $tasks)投递多个并发执行协程调度的任务
    （swoole_server->taskCo）

    通过 Task::async(array $tasks) 一次投递多个异步非并发任务

    通过 Task::run2($task, $method, $params)投递一个同步任务

    底层使用swoole_server->task()方法投递异步任务

    定时任务：

        首先在配置文件中开启定时任务：env('CRONABLE', true)

        定时任务和异步任务一样，只是不需要投递，在方法注解中加上@Scheduled注解即可

#### Bug&反馈

    1、工厂容器实例化对象的时候存在一个小bug

       bug内容：当container中的类存在__construct方法时，目前底层执行逻辑是，获
       取传入的construct方法参数，实例化对象，带着参数执行construct，此时，若
       construct方法中使用了配置文件中的属性，则会出现逻辑错误，因为此时配置文件
       中的配置并未写入对象中

       修复建议：先注入属性，再执行construct方法

       另外建议 routesFile属性解析一下别名

       备注：

        官方已修复，但修复方法简单粗暴，直接将routefile属性给去掉了，并且顺带去掉了一些其它的文档上没提到的属性...

    2、定时任务匹配存在bug

        目前的匹配规则匹配不了 @Scheduled(cron="*\/5 * * * * *")类似每5秒执 行一次这样的定时任务，因为源码中未考虑在注释中斜杠（/）需要转义，所以这种情况cron字符串  中会多一个反斜杠（\）

        修复建议：修改Swoft\Task\Crontab\ParseCrontable类中的正则表达式(\*(\/[0-9]+)?) 为 ((\*(\\\\\/[0-9]+)?)


    3、Swoft\Http\Message\Base\Request中的bug（低级错误）

        updateHostFromUri() 方法247行报错，经检查是第240行变量名写错了....