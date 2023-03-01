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

namespace shiyun\connection;

use app\supers\SuperToken;

/**
 * 依赖注入，应用配置类
 * 鉴权的时候 获取 【apps应用相关配置】
 * @author ctocode
 */
class OpenAppAccess
{
    public $accessData = [];
    public function setAccessData($data = [])
    {
        $token_access = syOpenAppsAuth('syOpenAppToken');

        SuperToken::sCacheSet(md5($token_access), json_encode($data));
        $this->accessData = $data;
    }
    public function setAccessName($key = '', $val = null)
    {
        $token_access = syOpenAppsAuth('syOpenAppToken');

        $tokenCache = SuperToken::sCacheGet(md5($token_access));
        $accessData = analysJsonDecode($tokenCache) ?? [];

        $accessData[$key] = $val;
        SuperToken::sCacheSet(md5($token_access), json_encode($accessData));
        $this->accessData = $accessData;
    }

    // 根据token  
    public function getAccessData()
    {
        $token_jwt = syOpenAppsAuth('Authorization');
        $token_access = syOpenAppsAuth('syOpenAppToken');

        /**
         * 是否解密token
         * @var boolean
         */
        if (!true) {
            $aes = new \ctocode\library\CtoAes();
            $token_access = $aes->decrypt(syOpenAppsAuth('syOpenAppToken'));
        }
        $tokenCache = SuperToken::sCacheGet(md5($token_access));
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
