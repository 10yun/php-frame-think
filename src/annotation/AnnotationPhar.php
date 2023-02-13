<?php

declare(strict_types=1);

namespace shiyun\annotation;

class AnnotationPhar
{
    public function getGlobData()
    {
        $pharFile = root_path() . "addons/base/base_account.phar";
        if (file_exists($pharFile)) {
            // echo '存在';
        }
        require $pharFile;
        require "phar://$pharFile";
    }
}
