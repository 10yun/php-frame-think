<?php

namespace shiyunQueue\annotation;

class AnnotationParse
{
    public function getDir($path)
    {
        if (empty($path)) {
            return [];
        }
        /**
         * 遍历注解目录
         */
        $queuePathArr = [];
        if (is_array($path)) {
            foreach ($path as $key => $val) {
                $queuePathArr = array_merge($queuePathArr, glob($val));
            }
        } else if (is_string($path)) {
            $queuePathArr[] = $path;
        }
        /**
         * 读取配置参数
         */
        foreach ($queuePathArr as $key => $val) {
        }
        return $queuePathArr;
    }
}
