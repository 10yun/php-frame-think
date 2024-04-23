<?php


declare(strict_types=1);

namespace shiyun\support;

use think\App;
use shiyun\support\Cache;
use shiyun\support\Env;
use shiyun\support\Config;

class Common
{
    protected $app;
    public function __construct(App $app)
    {
        $this->app = $app;
    }


    /**
     * 路径
     */
    protected string $addonPath = '';
    /**
     * 解析路由规则类型
     */
    public function parseRouteRuleType()
    {
        $requestObj = $this->app->request;
        $request_uri = $requestObj->baseUrl();

        if (!empty($request_uri) &&  str_contains($request_uri, "//")) {
            $request_uri = str_replace("//", "/", $request_uri);
        }
        // $request_uri = $requServer['REQUEST_URI'] ?? '';
        if ($request_uri == '/') {
            $this->addonPath = "";
            return '';
        }
        $pattern_http1 = '/^\/(\w+)\//'; // 正则 - 接口1
        $pattern_http2 = '/^\/(\w+)\.(\w+)\//'; // 正则 - 接口2
        $pattern_test1 = '/^\/tests\/(\w+)\//'; // 正则 - 测试1
        $pattern_test2 = '/^\/tests\/(\w+)\.(\w+)\//'; // 正则 - 测试2

        $envProjectEnvironment = frameGetEnv('ctocode.PROJECT_ENVIRONMENT');
        if ($envProjectEnvironment === 'development') {
            if (preg_match($pattern_test2, $request_uri, $matches)) {
                $this->addonPath = "addons/{$matches[1]}/$matches[2]/";
                return 'rule_test_2';
            }
            if (preg_match($pattern_test1, $request_uri, $matches)) {
                $this->addonPath = "addons/{$matches[1]}/";
                return 'rule_test_1';
            }
        }
        if (preg_match($pattern_http2, $request_uri, $matches)) {
            $this->addonPath = "addons/{$matches[1]}/$matches[2]/";
            return 'rule_http_2';
        }
        if (preg_match($pattern_http1, $request_uri, $matches)) {
            $this->addonPath = "addons/{$matches[1]}/";
            return 'rule_http_1';
        }
        return $this;
    }
    /**
     * 获取路由路径
     */
    public function getRoutePath()
    {
        $math_rule_type = $this->parseRouteRuleType();
        if (in_array($math_rule_type, [
            'rule_test_1', 'rule_test_2'
        ])) {
            return $this->addonPath . 'tests';
        }
        if (in_array($math_rule_type, [
            'rule_http_1', 'rule_http_2'
        ])) {
            return $this->addonPath . 'controller';
        }
    }
    /**
     * 获取路径-数据库
     */
    public function getConfigDbPath()
    {
        $math_rule_type = $this->parseRouteRuleType();
        if (in_array($math_rule_type, [
            'rule_test_1', 'rule_test_2',
            'rule_http_1', 'rule_http_2'
        ])) {
            return $this->addonPath . 'config/database.php';
        }
    }
}
