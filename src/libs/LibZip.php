<?php

namespace shiyun\libs;

use Exception;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use shiyunUtils\base\TraitModeInstance;

/**
 * 使用 ZipArchive 打包目录中的文件
 */
class LibZip
{
    use TraitModeInstance;
    /**
     * 密码
     */
    private string $password = '';
    /**
     * 设置密码
     */
    public function setPassword($str = '')
    {
        $this->password = $str;
        return $this;
    }
    /**
     * 压缩文件或目录到 ZIP 文件
     * 
     * @param string $source 要压缩的文件或目录路径
     * @param string $outputPath 生成的 ZIP 文件路径
     * @return bool 成功返回 true，失败返回 false
     */
    public function createZip(string $source, string $outputPath): bool
    {
        if (!extension_loaded('zip')) {
            throw new Exception('未加载 Zip PHP 扩展');
        }
        if (!file_exists($source)) {
            throw new Exception('源文件或目录不存在');
        }
        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            // if (!$zip->open($outputPath, ZipArchive::CREATE)) {
            throw new Exception('无法创建 ZIP 文件');
        }
        if (!empty($this->password)) {
            if (!$zip->setPassword($this->password)) {
                throw new Exception("无法设置密码");
            }
        }

        // 遍历目录并添加文件到 zip 中
        $source = realpath($source);
        if (is_dir($source)) {
            // 调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
            $this->addDirToZip($zip, $source, basename($source));
        } else if (is_file($source)) {
            $zip->addFile($source, basename($source));
        }
        // 完成打包
        // 关闭处理的zip文件 
        return $zip->close();
    }

    /**
     * 递归地将目录添加到 ZIP 文件中
     * 
     * @param ZipArchive $zip ZipArchive 实例
     * @param string $dir 要压缩的目录路径
     * @param string $baseDir 压缩包中的根目录名称
     */
    private function addDirToZip(ZipArchive $zip, string $dir, string $baseDir): void
    {
        $zip->addEmptyDir($baseDir);
        $files = new RecursiveIteratorIterator(
            // new RecursiveDirectoryIterator($dir),
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST, //   RecursiveIteratorIterator::LEAF_ONLY
        );
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $baseDir . DIRECTORY_SEPARATOR . $files->getSubPathName();
            // if (!$file->isDir()) {
            if (is_dir($filePath)) {
                $zip->addEmptyDir($relativePath);
            } else if (is_file($filePath)) {
                // 将文件添加到 zip 中
                $zip->addFile($filePath, $relativePath);
                if (!empty($this->password)) {
                    $zip->setEncryptionName($relativePath, ZipArchive::EM_AES_256);
                }
            }
        }
    }

    // function addDirToZip2($zip, $dir, $baseDir)
    // {
    //     $handler = opendir($dir); //打开当前文件夹由$path指定。
    //     while (($filename = readdir($handler)) !== false) {
    //         if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..’，不要对他们进行操作
    //             if (is_dir($dir . "/" . $filename)) { // 如果读取的某个对象是文件夹，则递归
    //                 $this->addDirToZip2($zip, $dir . "/" . $filename, '');
    //             } else {
    //                 //将文件加入zip对象
    //                 $path_save = str_replace("/www/storage/", "", $dir);
    //                 $zip->addFile($dir . "/" . $filename, $path_save . $filename);
    //             }
    //         }
    //     }
    //     @closedir($dir);
    // }

    /**
     * 提供下载功能
     * 
     * @param string $file 要下载的文件路径
     */
    public function downloadZip($file): void
    {
        if (!file_exists($file)) {
            throw new Exception('文件不存在');
        }
        // 设置下载头部
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        // 清空输出缓存，防止下载过程中出现问题
        ob_clean();
        flush();
        // 输出文件
        readfile($file);
        exit;
    }
}
