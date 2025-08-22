<?php

namespace shiyun\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class AddonsCheck extends Command
{
    // 验证 基础字段
    private $checkBaseArr = [];
    private $checkBaseKey = [];
    // 验证 dependencies 通用模块字段
    private $checkDepArr = [];
    private $checkDepKey = [];
    // 错误文件
    private $errorFileArr = [];
    protected function configure()
    {
        $this->setName('AddonsCheck')
            ->setDescription('check addons');
    }
    private function initCheckArr()
    {
        $this->checkBaseArr = [
            'name' => [
                'key' => 'name',
                'required' => true,
                'type' => 'string',
                'regular' => '/^[a-z\/_]+$/'
            ],
            'path' => [
                'key' => 'path',
                'required' => true,
                'type' => 'string',
                'regular' => '/^[a-z\/_]+$/'
            ],
            'version' => [
                'key' => 'version',
                'required' => true,
                'type' => 'string',
                'regular' => '/^v\d\.\d{2}\.\d{4}$/'
            ],
        ];
        foreach ($this->checkBaseArr as $key => $val) {
            if ($val['required']) {
                $this->checkBaseKey[] = $key;
            }
        }
        $this->checkDepArr = array_merge($this->checkBaseArr, [
            'type' => [
                'key' => 'type',
                'required' => true,
                'type' => 'string',
                'regular' => '/^(default|private)$/i'
            ],
        ]);
        foreach ($this->checkDepArr as $key => $val) {
            if ($val['required']) {
                $this->checkDepKey[] = $key;
            }
        }
    }
    protected function execute(Input $input, Output $output)
    {
        $this->initCheckArr();
        $module = glob(_PATH_PROJECT_ . 'addons/*/package.yml');
        $channelModule = glob(_PATH_PROJECT_ . 'addons/*/*/package.yml');
        $modules = array_merge($module, $channelModule);
        try {
            foreach ($modules as $file_key => $file_path) {
                if (is_file($file_path)) {
                    // 加载文件内容（这里假设是 YAML 文件，使用 yaml_parse_file）
                    $content = yaml_parse_file($file_path);
                    $checkRes = $this->checkItem($this->checkBaseArr, $this->checkBaseKey, $content);
                    if ($checkRes) {
                    } else {
                        $this->errorFileArr[] = $file_path;
                    }
                }
            }
            if (count($this->errorFileArr) > 0) {
                throw new \Exception("check addons error!");
            }
            $output->writeln("check addons ok! ");
        } catch (\Throwable $th) {
            //throw $th;
            $err_msg[] = str_pad("=", 30, "=");
            $err_msg[] = str_pad(" check addons error list ", 30);
            $err_msg[] = str_pad("-", 30, "-");
            $err_msg[] = var_export($this->errorFileArr, true);
            $err_msg[] = str_pad("-", 30, "-");
            $err_msg[] = "look docs：" . "https://docs.10yun.com/php/addons/package.html";
            $err_msg[] = str_pad("=", 30, "=");
            $output->writeln(implode("\n", $err_msg) . "\n");
        }
    }
    private function checkItem($checkKeyArr, $checkKeyExist, $content)
    {
        // 获取源数组的所有键
        $content_keys = array_keys($content);
        // 检查目标数组的每个值是否都存在于源数组的键中
        $intersection = array_intersect($checkKeyExist, $content_keys);
        if (count($intersection) !== count($checkKeyExist)) {
            return false;
        }

        foreach ($content as $key => $val) {
            if ($key == 'dependencies') {
                foreach ($val as $dep_key => $dep_val) {
                    $checkRes = $this->checkItem($this->checkDepArr, $this->checkDepKey, $dep_val);
                    if ($checkRes) {
                    } else {
                        return false;
                    }
                }
            } else {
                if (isset($checkKeyArr[$key])) {
                    $checkKey = $checkKeyArr[$key];
                    if ($checkKey['required'] && !isset($val)) {
                        return false;
                    }
                    if ($checkKey['type'] == 'string' && !is_string($val)) {
                        return false;
                    }
                    if (isset($checkKey['regular']) && !preg_match($checkKey['regular'], $val)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
