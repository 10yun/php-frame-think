# 文件-图片操作类

- 功能

| 方法        | 说明         | 详情 |
| :---------- | :----------- | :--- |
| setPath     | 设置路径     |      |
| getInfo     | 获取信息     |      |
| isCheck     | 验证图片类型 |      |
| doUpload    | 上传本地     |      |
| doCut       | 裁剪         |      |
| doWatermark | 水印         |      |



- 使用

```php

use shiyunUtils\libs\LibFileImg;

LibFileImg::方法();

# 或者

\shiyunUtils\libs\LibFileImg::方法();



## 判断是否图片
LibFileImg::setPath($imgPath)->isCheck();

 
```

## 获取图片信息

使用

```php
LibFileImg::setPath($imgPath)->getInfo();
```
返回
~~~
type： 类型
name： 名称
suffix： 后缀
~~~

