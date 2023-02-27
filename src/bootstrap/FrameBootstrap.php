<?php

declare(strict_types=1);

namespace shiyun\bootstrap;

use shiyun\support\Service as BaseService;

class FrameBootstrap extends BaseService
{
    public function boot()
    {
        $this->commands([
            'ConfigPush' => \shiyun\command\ConfigPush::class,
            'ServiceDiscover' => \shiyun\command\ServiceDiscover::class,
            'VendorPublish' => \shiyun\command\vendorPublish::class,
            // 'addons:model' => \shiyun\command\addons\Model::class,
            // 'addons:model_select' => \shiyun\command\addons\ModelSelect::class,
            // 'addons:rpc' => \shiyun\command\addons\Rpc::class,
            // 'addons:validate' => \shiyun\command\addons\Validate::class,
            // 'addons:crud' => \shiyun\command\addons\Crud::class,
            // 'addons:api' => \shiyun\command\addons\Api::class,
        ]);
    }
}
