<?php

declare(strict_types=1);

namespace shiyunOpensdk\connection;

/**
 * 使 模块 单独的 js、css、img 等资源 放置在一起
 *         与控制器，模型，试图，等一起模块化
 * @author ctocode-zhw
 * @version 2018-05-19
 */
class LoadAppResource
{
    public function getUI($addons, $resource)
    {
        if (empty($addons)) {
            return;
        }
        $pathinfo_str = request()->pathinfo();
        if (empty($pathinfo_str)) {
            return;
        }
        $fileInfo = pathinfo($pathinfo_str);
        if (empty($fileInfo)) {
            return;
        }
        if (empty($fileInfo['extension'])) {
            return;
        }
        $uiDataArr = explode($addons, $pathinfo_str);
        $path = sprintf(
            "/%s/addons/%s/public/%s",
            _PATH_PROJECT_,
            str_replace(".", "/", $addons),
            $uiDataArr[1]
        );
        $path = str_replace("//", "/", $path);
        if (!file_exists($path)) {
            return sendRespError('Resource Not Found');
        }
        switch ($fileInfo['extension']) {
            case 'jpg':
            case 'png':
            case 'jpeg':
            case 'gif':
            case 'ico':
                cc_ui_output_imageFromPath($path);
                break;
            case 'css':
                cc_ui_output_cssFromPath($path);
                break;
            case 'js':
                cc_ui_output_jsFromPath($path);
                break;
        }
    }
}
