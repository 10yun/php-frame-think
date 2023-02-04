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
            'VendorPublish' => \shiyun\command\vendorPublish::class,
            // 'cmake:model' => \shiyun\command\Model::class,
            // 'cmake:model_select' => \shiyun\command\ModelSelect::class,
            // 'cmake:rpc' => \shiyun\command\Rpc::class,
            // 'cmake:validate' => \shiyun\command\Validate::class,
            // 'cmake:crud' => \shiyun\command\Crud::class,
            // 'cmake:api' => \shiyun\command\Api::class,
        ]);
    }
}
