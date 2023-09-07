<?php

namespace shiyun\command\make;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

trait TraitCommon
{ //
    protected function getClassName($nameInput = '')
    {
        $className = '';
        if (str_contains($nameInput, "/")) {
            $nameArr = explode("/", $nameInput);
            $className =  $nameArr[1] ?? '';
            $className = ucfirst($className);
        } else {
            $className = ucfirst($nameInput);
        }
        return $className;
    }
    protected function getAnnoFlag($dir = '', $nameInput = '', $roleInput = '')
    {
        $tempArr = [];
        if (str_contains($dir, "/")) {
            $tempArr = explode("/", $dir);
        }
        $roleMate = [
            'business' => 'BUS',
            'platform' => 'PLAT',
            'admin' => 'ADMIN',
            'agent' => 'AGENT',
            'tourist' => 'TOU',
            'user' => 'UC',
        ];
        if (!empty($roleMate[$roleInput])) {
            $tempArr[] = $roleMate[$roleInput];
        }
        $contrArr = preg_split("/(?=[A-Z])/", $nameInput);
        $tempArr = array_merge($tempArr, $contrArr);
        $lastArr = [];
        foreach ($tempArr as $val) {
            if (!empty($val)) {
                $lastArr[] = strtoupper($val);
            }
        }
        return implode("_", $lastArr);
    }
    protected function getAnnoRestful($dir = '', $nameInput = '', $roleInput = '')
    {
        $tempArr = [];
        if (str_contains($dir, "/")) {
            $tempArr[] = str_replace("/", ".", $dir);
        }
        $tempArr[] = $roleInput;
        $contrArr = preg_split("/(?=[A-Z])/", $nameInput);
        $tempArr = array_merge($tempArr, $contrArr);
        $lastArr = [];
        foreach ($tempArr as $key => $val) {
            if (!empty($val)) {
                $lastArr[] = strtolower($val);
            }
        }
        return implode("_", $lastArr);
    }
    protected function getNamespaceController($dir = '', $nameInput = '', $roleInput = '')
    {
        $namespaceArr = [];
        $namespaceArr[] = 'addons';
        $dir = strtolower($dir);
        array_push($namespaceArr, ...explode("/", $dir));
        $namespaceArr[] = 'controller';
        if (!empty($roleInput)) {
            $namespaceArr[] = strtolower($roleInput);
        }
        if (str_contains($nameInput, "/")) {
            $nameArr = explode("/", $nameInput);
            $namespaceRole = $nameArr[0]  ?? '';
            $namespaceArr[] = strtolower($namespaceRole);
        }
        return $namespaceArr;
    }
    protected function getNamespaceModel($dir = '', $nameInput = '')
    {
        $namespaceArr = [];
        $namespaceArr[] = 'addons';
        $dir = strtolower($dir);
        array_push($namespaceArr, ...explode("/", $dir));
        $namespaceArr[] = 'models';
        return $namespaceArr;
    }
    protected function getNamespaceValidate($dir = '', $nameInput = '')
    {
        $namespaceArr = [];
        $namespaceArr[] = 'addons';
        $dir = strtolower($dir);
        array_push($namespaceArr, ...explode("/", $dir));
        $namespaceArr[] = 'validate';
        return $namespaceArr;
    }

    protected function getNamespaceServer($dir = '', $nameInput = '')
    {
        $namespaceArr = [];
        $namespaceArr[] = 'addons';
        $dir = strtolower($dir);
        array_push($namespaceArr, ...explode("/", $dir));
        $namespaceArr[] = 'services';
        if (str_contains($nameInput, "/")) {
            $nameArr = explode("/", $nameInput);
            $namespaceRole = $nameArr[0]  ?? '';
            $namespaceArr[] = strtolower($namespaceRole);
        } else {
            $namespaceArr[] = ucfirst($nameInput);
        }
        return $namespaceArr;
    }
}
