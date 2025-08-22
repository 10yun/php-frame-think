<?php

namespace shiyun\libs;

use shiyun\support\Cache;
use shiyun\support\Db;

class ResponseCode
{
    use TraitModeInstance;
    /**
     * @action
     * 根据9宫格，根据 厂商+应用+控制器，转译str为数字
     *  如 errorStr = 'pc/member/card' 对应9宫格数字为 726362372273
     *
     *  1       2[abc] 3[def]
     *  4[ghi]  5[jkl] 6[mno]
     *  7[pqrs] 8[tuv] 9[wxyz]
     * 
     */

    // if (empty($xxxx)) {
    // 	throw new \Exception('邀请码不存在', 100006);
    // }

    // 200 => '请求成功',
    // 404 => '系统异常',
    // 502 => '数据库异常',
    // 505 => '系统bug',

    // 	1049 => '错误位置!',
    // 	1050 => '抱歉,出错啦!',
    // 	1051 => '跳转提示',
    // 	1052 => '立即跳转',
    // 	1053 => '停止跳转',

    // 	/* 用户中心 */
    // 	10001 => '用户未登录',
    // 	10002 => '用户登录过期',
    // 	10003 => '用户登录失败',

    protected $codeArr = [
        0 => '状态码错误',
        100000 => '请求异常',
        100010 => 'code异常',
        100020 => 'model异常',
        100030 => '请勿重复提交',
        100101 => '没有凭证，请登录',
        100102 => '无效凭证，请重新登录',
        100103 => '凭证过期，请重新登录',
        100104 => '信息不存在，请重新登录',
        100109 => 'Token错误', // syOpenAppToken
        100201 => 'AppProject 不通过', // syOpenAppProject  
        100202 => 'AppId 不通过', //应用ID错误 syOpenAppId
        100203 => 'AppSecret 不通过',
        100206 => '角色错误', // syOpenAppRole 、 AppRole

        100905 => '账号不存在',
        100910 => '账户禁用',
        100910 => '用户违规操作已被停用，请联系平台',
        100920 => '商户禁用',
        100930 => '员工禁用',
        /**
         * 业务-登录相关
         */
        400130 => '图形验证码错误或已过期',
        400180 => '未注册绑定', // UCENTER_REGISTER_NOT
        400200 => '手机号不存在', // UCENTER_MOBILE_UNKNOWN


        900000 => '系统维护中',

    ];
    protected function getConfig()
    {
        $root_path = root_path();
        $configData = include_once $root_path . '/config/shiyun/response.php';
        $this->codeArr = array_merge($this->codeArr, $configData['code'] ?? []);
        return $this->codeArr;
    }
    public function getCodeArr()
    {
        // $ResponsCodeArr = Cache::get('ResponsCode');
        // if (empty($ResponsCodeArr)) {
        // 	$ResponsCodeArr = Db::table('base__dict_response_code_zh')->select();
        // 	Cache::Set('ResponsCode', $ResponsCodeArr);
        // } 
        return $this->codeArr;
    }
    public function codeToMessage(int $code)
    {
        $codeArr = $this->getCodeArr();
        $msg = $codeArr[$code] ?? '暂无该状态码信息';
        return $msg;
    }
}
