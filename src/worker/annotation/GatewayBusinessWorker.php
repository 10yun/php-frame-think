<?php

declare(strict_types=1);

namespace shiyunWorker\annotation;

use Attribute;
use \Workerman\Worker;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class GatewayBusinessWorker
{
    public function __construct(
        string $address = '',
        string $name = '',
        int $count = 1,
        string $eventHandler = ''
    ) {
        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称
        // 获取 调用该注解的类的名称
        $className = '';
        $worker->name = !empty($name) ? $name : $className . 'BusinessWorker';
        // bussinessWorker进程数量
        $worker->count = $count;
        // 服务注册地址
        $worker->registerAddress = $address;

        $worker->$eventHandler =  !empty($name) ? $name : $className::class;
    }
}
