<?php

/**
 * 【ctocode】     核心文件
 * ============================================================================
 * @author       作者      trystan
 * @copyright    版权所有   2015-2027，并保留所有权利。
 * @copyright    版权所有   【福州十云科技有限公司】，并保留所有权利。
 * @link         网站地址   https://www.10yun.com
 * @contact      联系方式   QQ:343196936
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

declare(strict_types=1);

namespace shiyunOpensdk\connection;

use shiyun\support\Cache;
use shiyun\support\Db;

/**
 * 依赖注入，应用配置类
 * 鉴权的时候 获取 【apps应用相关配置】
 * @author ctocode
 */
class OpenAppConfig
{
    public $sdkData;

    public function __construct()
    {
        // $headerData = request()->header();
        $this->setFlag();
    }
    public function setSett($settData = [])
    {
        $this->sdkData['lives'] = $settData['lives'] ?? [];
        $this->sdkData = $settData;
    }
    // 设置应用标示
    public function setFlag($diyApp = '')
    {
        $defAppConf = [];
        if (!empty($diyApp)) {
            $defAppConf = syGetAppsSett($diyApp);
        }
        //
        $syOpenAppProject = syOpenAppsAuth('syOpenAppProject');
        $diyProFlag = $syOpenAppProject;
        $diyProConf = syGetProjectSett($diyProFlag);
        //
        $syOpenAppId = syOpenAppsAuth('syOpenAppId');
        $diyAppFlag = !empty($diyApp) ? $diyApp : $syOpenAppId;
        $diyAppConf = syGetAppsSett($diyAppFlag);

        $cacheData =  array_merge($defAppConf, $diyProConf, $diyAppConf);
        $cacheKey = 'auth_app_' . $diyAppFlag;
        // $cacheData = Cache::get ( $cacheKey );
        // 缓存不存在的话
        if (empty($cacheData)) {
            // sendRespError('联系管理员');
            // $cacheData = array_merge($defData, $cacheData);
            // Cache::set($cacheKey, $cacheData);
        }
        $this->sdkData = $cacheData;
    }
    public function getSett()
    {
        // 如果没有，取默认所有配置
        return $this->sdkData;
    }
}
