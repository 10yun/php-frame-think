<?php

/**
 * 【ctocode】     核心文件
 * ============================================================================
 * @author       作者         ctocode-zhw
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @copyright    版权所有   【福州十云科技有限公司】，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936 ，QQ:240337740
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

declare(strict_types=1);

namespace shiyunOpensdk\connection;

use think\App;
use shiyun\support\Cache;
use shiyun\support\Env;
use shiyun\support\Config;

/**
 * 加载数据库配置
 */
class LoadAddonsDb
{
    protected $app;
    public function __construct(App $app)
    {
        $this->app = $app;
        // 加载addons下的
        $this->loadAddons();
        // 加载project下的
        // $this->loadProject();
    }
    public function loadAddons()
    {
        /**
         * 数据库
         */
        $supportCommon = new \shiyun\support\Common($this->app);
        $routePath = $supportCommon->getConfigDbPath();
        if (!empty($routePath)) {
            $db_diy_path = root_path() . $routePath;
            if (!empty($db_diy_path) && file_exists($db_diy_path)) {
                $db_diy_conf = include $db_diy_path;
                if (!empty($db_diy_conf)) {
                    Config::set($db_diy_conf, 'database');
                }
            }
        }
    }
}
