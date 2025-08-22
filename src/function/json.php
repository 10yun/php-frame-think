<?php


function cc_json_zh_encode(array $data = [])
{
    // JSON_PRETTY_PRINT 格式化
    // JSON_UNESCAPED_UNICODE 保留中文
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}
/**
 * array 转 json
 * @return string
 */
function cc_json_encode(array $data = [])
{
    return json_encode($data);
}
