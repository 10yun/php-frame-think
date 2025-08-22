<?php

/**
 * 判断是否是 键值对，一维
 */
function cc_array_is_dimension($data)
{
    // $filteredArray = array_filter(array_keys($data), 'is_string');
    $filteredArray = array_filter($data, 'is_string', ARRAY_FILTER_USE_KEY);
    if (count($filteredArray) === count($data)) {
        // 判断为一维键值对数组的逻辑
        return true;
    }
    return false;
}
