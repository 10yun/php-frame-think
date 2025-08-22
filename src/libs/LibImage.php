<?php

namespace shiyun\libs;

use Exception;
use think\Image as ThinkImage;

class LibImage
{
    use TraitModeInstance;
    /**
     * 是否销毁原图
     */
    protected bool $isOriginalDestroy = false;
    public function setOriginalDestroy(bool $isState = false)
    {
        $this->isOriginalDestroy = $isState;
        return $this;
    }
    /**
     * 设置原图路径
     * 自动判断是本地还是远程
     */
    protected string $originalPath = '';
    public function setOriginalPath(string $path = '')
    {
        $this->originalPath = trim($path);
        return $this;
    }
    public function crop($w, $h, $x, $y)
    {
        $temp_save_dir = _PATH_FILE_ . 'tmp_cut/';
        $old_path = $this->originalPath;
        if (cc_is_domain_url($this->originalPath)) {
            // 远程
            $downResult = ctoImgRemoteDown($this->originalPath, $temp_save_dir);
            $old_path = $downResult['save_path'];
            $temp_new_path = uniqid(time(), true) . $downResult['ext'];
        }
        $image = ThinkImage::open($old_path);
        // 将图片裁剪为300x300并保存为crop.png
        $image->crop($w, $h, $x, $y)->save($temp_new_path);
        if ($this->isOriginalDestroy) {
            @unlink($old_path);
        }
        $cut_after_content = file_get_contents($temp_new_path);
        return [
            'content' => $cut_after_content,
        ];
        $imgCheck = ctoImgCheck($fileObj['tmp_name']);
        if ($imgCheck['type'] != 'ok') {
            exit(json_encode($imgCheck));
        }
    }
}
