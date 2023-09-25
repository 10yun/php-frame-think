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

/**
 * 依赖注入，应用配置类
 * 鉴权的时候 获取 【apps应用相关配置】
 * @author ctocode
 */
class OpenAppAccess
{
    protected $setCacheStore = 'CACHE_STORES_REDIS';
    protected $setCacheKey = '_token_';
    protected $setCacheTime =  (60 * 60 * 24) * 3; // 缓存3天

    public function sCacheGet($diyKey = '', $needField = [])
    {
        $setCacheKey = $this->setCacheKey;
        $key_project = syOpenAppsAuth('syOpenAppProject');
        $key_apps = syOpenAppsAuth('syOpenAppId');
        $cacheKey = $key_project . ':' . $key_apps . ':' . $setCacheKey . $diyKey;
        $cacheData = frameCacheGet('CACHE_STORES_RD2', $cacheKey);
        return $cacheData;
    }
    public function sCacheSet($diyKey = '', $data = null)
    {
        $setCacheKey = $this->setCacheKey;
        $key_project = syOpenAppsAuth('syOpenAppProject');
        $key_apps = syOpenAppsAuth('syOpenAppId');
        $cacheKey = $key_project . ':' . $key_apps . ':' . $setCacheKey . $diyKey;
        frameCacheSet('CACHE_STORES_RD2', $cacheKey, $data, (60 * 60 * 24 * 3));
    }

    public $accessData = [];
    public function setAccessData($data = [])
    {
        $token_access = syOpenAppsAuth('syOpenAppToken');
        $this->sCacheSet(md5($token_access), json_encode($data));
        $this->accessData = $data;
    }
    public function setAccessName($key = '', $val = null)
    {
        $token_access = syOpenAppsAuth('syOpenAppToken');

        $tokenCache = $this->sCacheGet(md5($token_access));
        $accessData = analysJsonDecode($tokenCache) ?? [];

        $accessData[$key] = $val;
        $this->sCacheSet(md5($token_access), json_encode($accessData));
        $this->accessData = $accessData;
    }

    // 根据token  
    public function getAccessData()
    {
        $token_jwt = syOpenAppsAuth('Authorization');
        $token_access = syOpenAppsAuth('syOpenAppToken');

        /**
         * 是否解密token
         */
        if (!true) {
            $aes = new \shiyunUtils\libs\LibsSymmAES();
            $token_access = $aes->decrypt(syOpenAppsAuth('syOpenAppToken'));
        }
        $tokenCache = $this->sCacheGet(md5($token_access));
        if (empty($tokenCache)) {
            return [];
        }
        $this->accessData = analysJsonDecode($tokenCache) ?? [];
        if (!empty($this->accessData) && is_array($this->accessData)) {
            $this->accessData['account_id'] = $this->accessData['account_id'] ?? 0;
            return $this->accessData;
        }
    }
}
