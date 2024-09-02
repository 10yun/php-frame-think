<?php

namespace shiyunQueue\process;

interface InterfaceProcess
{
    // 处理 - 初始化
    public function dealInit($consumeClassOpt = []);
    // 处理 - 验证
    public function dealItemCheck($file);
    // 处理 - 数据
    public function dealItemData($consumeClassObj, $consumeClassOpt, $consumeQeObj);
}
