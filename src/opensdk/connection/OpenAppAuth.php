<?php

namespace shiyunOpensdk\connection;

class OpenAppAuth
{
    /**
     * 依赖注入，应用配置类
     * 鉴权的时候 获取 【apps应用相关配置】
     * @author ctocode
     */
    public $authData = [];
    public function __construct() {}
    public function initAuthData()
    {
        $fwParam = [];
        $fwParam['syOpenAppProject'] = $this->getAppProject();
        $fwParam['syOpenAppRole'] = $this->getAppRole();
        $fwParam['syOpenAppId'] = $this->getAppId();
        $fwParam['syOpenAppSecret'] = $this->getAppKey();
        $fwParam['syOpenAppToken'] = $this->getAppToken();
        // 设备 类型：ios、android
        $fwParam['syAppClientPlatform'] = $this->getAppClientPlatform();
        // 获取设备唯一标识
        $fwParam['syAppClientUUID'] = $this->getAppClientUuid();
        $fwParam['syAppClientId'] = $this->getAppClientId();
        $this->setAuthData($fwParam);
    }
    public function setAuthData($data = [])
    {
        $this->authData = $data;
    }
    public function getAuthData()
    {
        return $this->authData;
    }

    public function getAppProject()
    {
        /**
         * 处理 syOpenAppProject
         * 如果没有header数据，获取 $reqParam['syOpenAppProject'] 数据
         */
        if ($project = \shiyun\libs\Console::getInstance()->getOption('syOpenAppProject')) {
            return $project;
        }
        $reqVal = request()->param('syOpenAppProject') ?? request()->param('sy_open_app_project');
        $headVal = request()->header('syOpenAppProject') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function setAppProject($str = '')
    {
        $this->authData['syOpenAppProject'] = $str;
        return $this;
    }
    public function getAppId()
    {
        /**
         * 处理 syOpenAppId
         * 如果没有header数据，获取 $reqParam['syOpenAppId'] 数据
         */
        $reqVal = request()->param('syOpenAppId') ?? request()->param('sy_open_app_id');
        $headVal = request()->header('syOpenAppId') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function setAppId($str = '')
    {
        $this->authData['syOpenAppId'] = $str;
        return $this;
    }
    public function getAppKey()
    {
        /**
         * 处理 syOpenAppSecret
         * 如果没有header数据，获取 $reqParam['syOpenAppSecret'] 数据
         */
        $reqVal = request()->param('syOpenAppSecret') ?? request()->param('sy_open_app_secret');
        $headVal = request()->header('syOpenAppSecret') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function setAppKey($str = '')
    {
        $this->authData['syOpenAppSecret'] = $str;
        return $this;
    }
    public function getAppRole()
    {
        /**
         * 处理 syOpenAppRole
         * 如果没有header数据，获取 $reqParam['syOpenAppRole'] 数据
         */
        $reqVal = request()->param('syOpenAppRole') ?? request()->param('sy_open_app_role');
        $headVal = request()->header('syOpenAppRole') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function getAppToken()
    {
        /**
         * 处理 syOpenAppToken
         * 如果没有header数据，获取 $reqParam['syOpenAppToken'] 数据
         */
        $reqVal = request()->param('syOpenAppToken') ?? request()->param('sy_open_app_token');
        $headVal = request()->header('syOpenAppToken') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function getAppClientPlatform()
    {
        $reqVal = '';
        $headVal = request()->header('sy-client-platform') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function getAppClientUuid()
    {
        $reqVal = '';
        $headValUuid = request()->header('sy-client-uuid') ?: '';
        $headValId = request()->header('sy-client-id') ?: '';
        $lastVal = !empty($headValUuid)
            ? $headValUuid
            : $headValId;
        return $lastVal;
    }
    public function getAppClientId()
    {
        $reqVal = '';
        $headValUuid = request()->header('sy-client-uuid') ?: '';
        $headValId = request()->header('sy-client-id') ?: '';
        $lastVal = !empty($headValUuid)
            ? $headValUuid
            : $headValId;
        return $lastVal;
    }
}
