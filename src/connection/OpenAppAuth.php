<?php

namespace shiyun\connection;

class OpenAppAuth
{
    /**
     * 依赖注入，应用配置类
     * 鉴权的时候 获取 【apps应用相关配置】
     * @author ctocode
     */
    public $authData = [];
    public function __construct()
    {
    }
    public function initAuthData()
    {
        $fwParam = [];
        $fwParam['syOpenAppProject'] = $this->getAppProject();
        $fwParam['syOpenAppRole'] = $this->getAppRole();
        $fwParam['syOpenAppId'] = $this->getAppId();
        $fwParam['syOpenAppKey'] = $this->getAppKey();
        $fwParam['syOpenAppToken'] = $this->getAppToken();
        // 设备 类型：ios、android
        $fwParam['syAppClientPlatform'] = $this->getAppClient();
        // 获取设备唯一标识
        $fwParam['syAppClientUUID'] = '';
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
        $reqVal = request()->param('syOpenAppProject');
        $headVal = request()->header('syOpenAppProject') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function getAppId()
    {
        /**
         * 处理 syOpenAppId
         * 如果没有header数据，获取 $reqParam['syOpenAppId'] 数据
         */
        $reqVal = request()->param('syOpenAppId');
        $headVal = request()->header('syOpenAppId') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function getAppKey()
    {
        /**
         * 处理 syOpenAppKey
         * 如果没有header数据，获取 $reqParam['syOpenAppKey'] 数据
         */
        $reqVal = request()->param('syOpenAppKey');
        $headVal = request()->header('syOpenAppKey') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
    public function getAppRole()
    {
        /**
         * 处理 syOpenAppRole
         * 如果没有header数据，获取 $reqParam['syOpenAppRole'] 数据
         */
        $reqVal = request()->param('syOpenAppRole');
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
        $reqVal = request()->param('syOpenAppToken');
        $headVal = request()->header('syOpenAppToken') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }


    public function getAppClient()
    {
        $reqVal = '';
        $headVal = request()->header('syOpenAppClientPlatform') ?: '';
        $lastVal = $headVal ?: ($reqVal ?? '');
        return $lastVal;
    }
}
