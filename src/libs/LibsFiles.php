<?php

namespace shiyun\libs;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use CallbackFilterIterator;

class LibsFiles
{
    /**
     * 迭代器目录复制
     */
    public static function RecursiveCopyDirectory($source, $destination)
    {
        // 创建目标目录
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        // 创建遍历迭代器
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {

            $targetPath = $destination . '/' . $iterator->getSubPathName();

            if ($item->isDir()) {
                // 创建对应的目标子目录
                if (!file_exists($targetPath)) {
                    mkdir($targetPath);
                }
            } else {
                // 复制文件
                copy($item, $targetPath);
            }
        }
    }
    /**
     * 迭代器目录删除
     */
    public static function RecursiveDelteDirectory($directory, array $filterFiles)
    {
        $filterCallback = function ($file, $key, $iterator) use ($filterFiles) {
            return !in_array(basename($file->getPathname()), $filterFiles);
        };

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $filteredIterator = new CallbackFilterIterator($iterator, $filterCallback);

        foreach ($filteredIterator as $name => $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }
    }
}
