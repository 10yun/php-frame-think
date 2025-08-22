<?php

namespace shiyun\libs;

/**
 * 图片操作类
 */
class LibsFileImg extends LibsFileFactory
{
    /**
     * 图片 裁剪
     * @author ctocode-zhw
     * @version 2015-08-16
     * @param string $oldfile   原图
     * @param string $newfile   保存位置 留空则输入图片头
     * @param array  $cutOpt = array( 裁剪参数
     * 					'width'=>'',裁剪宽度
     * 					'height'=>'',裁剪高度
     * 					'x'=>'',裁剪起点X坐标
     * 					'y'=>'',裁剪起点Y坐标
     * 				  ) 
     * 				 array	
     * @param number $quality  图片质量
     * @param boolean $sharp   是否锐化
     * @return boolean
     */
    public function doCut($oldfile = '', $newfile = '', $cutOpt = array(
        'width' => 100,
        'height' => 100,
        'x' => 0,
        'y' => 0
    ), $quality = 100, $sharp = false)
    {
        if (!is_string($oldfile) || $oldfile == '' || !is_file($oldfile)) {
            return false;
        }
        // 验证图片
        $imgCheck = ctoImgCheck($oldfile);
        if ($imgCheck['type'] != 'ok')
            return false;

        $old_width = $imgCheck['imgData'][0];
        $old_height = $imgCheck['imgData'][1];
        if (!$old_width || !$old_height) {
            return false;
        }
        switch ($imgCheck['imgData']['mime']) {
            case 'image/gif':
                $creationFunction = 'ImageCreateFromGif';
                $outputFunction = 'ImagePng';
                $mime = 'image/png';
                $doSharpen = false;
                break;
            case 'image/x-png':
            case 'image/png':
                $creationFunction = 'ImageCreateFromPng';
                $outputFunction = 'ImagePng';
                $doSharpen = false;
                break;
            default:
                $creationFunction = 'ImageCreateFromJpeg';
                $outputFunction = 'ImageJpeg';
                $doSharpen = true;
                break;
        }
        if (function_exists($creationFunction) && function_exists($outputFunction)) {
            $save_width = min($old_width, $cutOpt['width']);
            $save_height = min($old_height, $cutOpt['height']);
            if ($cutOpt['x'] + $save_width >= $old_width) {
                $cutOpt['x'] = $old_width - $save_width;
            }
            $cutOpt['x'] = max(0, $cutOpt['x']);
            if ($cutOpt['y'] + $save_height >= $old_height) {
                $cutOpt['y'] = $old_height - $save_height;
            }
            $cutOpt['y'] = max(0, $cutOpt['y']);
            $src = $creationFunction($oldfile);
            $dst = imagecreatetruecolor($save_width, $save_height);
            if (function_exists('ImageCopyResampled')) {
                imagecopyresampled($dst, $src, 0, 0, $cutOpt['x'], $cutOpt['y'], $save_width, $save_height, $save_width, $save_height);
            } else {
                imagecopyresized($dst, $src, 0, 0, $cutOpt['x'], $cutOpt['y'], $save_width, $save_height, $save_width, $save_height);
            }
            if ($sharp) {
                $dst = imgSharp($dst, 0.2);
            }
            if ($newfile != '') {
                if ($outputFunction == 'ImageJpeg') {
                    $outputFunction($dst, $newfile, $quality);
                } else {
                    $outputFunction($dst, $newfile);
                }
            } else {
                ob_start();
                if ($outputFunction == 'ImageJpeg') {
                    $outputFunction($dst, null, $quality);
                } else {
                    $outputFunction($dst);
                }
                $data = ob_get_contents();
                ob_end_clean();
                header("Content-type: $mime");
                header('Content-Length: ' . strlen($data));
                echo $data;
            }
            imagedestroy($dst);
            imagedestroy($src);
            return true;
        }
        return false;
    }
    public function doWatermark($groundImage, $waterPos = 0, $waterImage = "", $waterText = "", $textFont = 14, $textColor = "#FF0000")
    {
        // return imagewatermark_func($groundImage, $groundImage, $waterPos, $waterImage, $waterText, 95, $textFont, $textColor);
    }
}
