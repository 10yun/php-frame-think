<?php

namespace shiyunQueue\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class QueueModePubsub
{
    //  消息队列的使用过程，如下：
    //  1.客户端连接到消息队列服务器，打开一个channel。
    //  2.客户端声明一个exchange，并设置相关属性。
    //  3.客户端声明一个queue，并设置相关属性。
    //  4.客户端使用routing key，在exchange和queue之间建立好绑定关系。
    //  5.客户端投递消息到exchange。
    //  exchange接收到消息后，就根据消息的key和已经设置的binding，进行消息路由，将消息投递到一个或多个队列里。
}
