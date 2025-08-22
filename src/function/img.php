<?php

/**
 * 【ctocode】      常用函数 - img相关处理
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @version 	  版本	  v5.7.1.20210514
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936
 */

/**
 * 图片验证
 * @author ctocode-zhw
 * @version 2016-08-06 
 * @param string $img_path 图片路径
 * @return array(
 *  extension 类型
 *  filename  名字
 *  basename  文件名
 * )
 */
function ctoImgCheck($img_path = '')
{
    // 获取图片信息大小
    $imgData = getimagesize($img_path, $info);
    if (!in_array($imgData['mime'], array(
        'image/pjpeg',
        'image/jpeg',
        'image/gif',
        'image/png',
        'image/x-png',
        'image/xpng',
        'image/bmp',
        'image/wbmp'
    ), true)) {
        return array(
            'type' => 'no',
            'msg' => '图片类型错误'
        );
    }
    // 获取后缀名
    $_mime = explode('/', $imgData['mime']);
    $_ext = '.' . end($_mime);
    $imgData2 = pathinfo(parse_url($img_path)['path']);
    return array(
        'type' => 'ok',
        'filename' => $imgData2['filename'],
        'extension' => $_ext,
        'basename' => $imgData2['basename'],
        'imgData' => $imgData
    );
}
// 远程下载2
function ctoImgRemoteDown2($url, $path = 'images/')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
    $filename = pathinfo($url, PATHINFO_BASENAME);
    $resource = fopen($path . $filename, 'a');
    fwrite($resource, $file);
    fclose($resource);
}

/**
 * 远程图片下载
 * @author ctocode-zhw
 * @version 2018-05-31
 * @param string $imgUrl  图片url地址
 * @param string $saveDir 本地存储路径 默认存储在当前路径
 * @param string $fileName 图片存储到本地的文件名 ,当保存文件名称为空时则使用远程文件原来的名称
 * @return mixed
 */
function ctoImgRemoteDown($imgUrl, $saveDir = './', $fileName = null)
{
    if (trim($imgUrl) == '') {
        return false;
    }
    if (preg_match("#^//\w+#", $imgUrl)) {
        $imgUrl = 'http:' . $imgUrl;
    }
    if (preg_match('/(http:\/\/)|(https:\/\/)/i', $imgUrl)) {
        $imgUrl = preg_replace('/(http:\/\/)|(https:\/\/)/i', 'http://', $imgUrl);
    }
    // 验证图片
    $imgCheck = ctoImgCheck($imgUrl);
    if ($imgCheck['type'] != 'ok')
        return $imgCheck;

    if (empty($fileName)) {
        // 生成唯一的文件名
        // $fileName = uniqid ( time (), true ) . $imgCheck['extension'];
        $fileName = $imgCheck['basename'];
    }

    // 开始抓取远程图片
    ob_start();
    readfile($imgUrl);
    $imgInfo = ob_get_contents();
    ob_end_clean();

    if (!file_exists($saveDir)) {
        mkdir($saveDir, 0777, true);
    }
    $fp = fopen($saveDir . $fileName, 'a');
    $imgLen = strlen($imgInfo); // 计算图片源码大小
    $_inx = 1024; // 每次写入1k
    $_time = ceil($imgLen / $_inx);
    for ($i = 0; $i < $_time; $i++) {
        fwrite($fp, substr($imgInfo, $i * $_inx, $_inx));
    }
    fclose($fp);

    return array(
        'ext' => $imgCheck['extension'],
        'file_name' => $fileName,
        'save_path' => $saveDir . $fileName
    );
}
