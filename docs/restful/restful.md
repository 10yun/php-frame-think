

## restfule 接口风格


### 说明

| 请求method | 接口路由 url | 对应controller方法 | 说明                                             |
| :--------- | :----------- | :----------------- | :----------------------------------------------- |
| GET        | /zoos        | getData            | 列出所有动物园                                   |
| GET        | /zoos/ID     | getById            | 获取某个指定动物园的信息                         |
| POST       | /zoos        | postData           | 新建一个动物园                                   |
| POST       | /zoos/ID     | postById           | 更新一个动物园                                   |
| PUT        | /zoos/ID     | putById            | 更新某个指定动物园的信息(提供该动物园的全部信息) |
| PATCH      | /zoos/ID     | patchById          | 更新某个指定动物园的信息(提供该动物园的部分信息) |
| DELETE     | /zoos/ID     | deleteById         | 删除某个动物园                                   |


### 示例

```php

<?php

namespace app\common\controller;

class RestApi
{
    // GET /con/ID：获取某个指定的数据
    public function getById(int $id = 0)
    {
    }
    // GET /con：列出数据
    public function getData()
    {
    }
    // POST /con：新建一条数据
    public function postData()
    {
    }
    // PUT /con/ID：更新某条数据（全部信息
    public function putById(int $id = 0)
    {
    }
    // PATCH /con/ID：更新某条数据（部分信息
    public function patchById(int $id = 0)
    {
    }
    // DELETE /con/ID：删除某条数据
    public function deleteById(int $id = 0)
    {
    }
}
```


### 其他参考

https://blog.csdn.net/weixin_41120504/article/details/115638094
https://blog.csdn.net/weixin_35936248/article/details/111931540
 
