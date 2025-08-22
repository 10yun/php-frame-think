# 随机生成

- 功能

| 方法         | 说明           | 详情              |
| :----------- | :------------- | :---------------- |
| doNumBase    | 生成数字       | 默认4位           |
| doNumMd5     | 生成数字+字母  | 暂无功能          |
| doLetterBase | 生成字母       | 大小字母，默认4位 |
| doLetterMd5  | 生成字母 + md5 | 大小字母，默认4位 |
| doNumLetter  | 生成数字+字母  | 字母+数字，默认32 |



- 使用

```php

use shiyunUtils\helper\HelperRandom;

HelperRandom::方法();

# 或者

\shiyunUtils\helper\HelperRandom::方法();

```