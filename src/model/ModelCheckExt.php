<?php

namespace shiyun\model;

use think\Model;
use think\facade\Db;
use shiyun\model\exception\ModelCheckException;
use shiyun\model\ModelCheckRule;
use Closure;

class ModelCheckExt extends Model
{
    protected $table = '';
    protected $pk = '';
    /**
     * 当前验证场景
     */
    protected string $currentScene;

    protected array $only = [];
    protected array $append = [];
    protected array $remove = [];

    /**
     * 验证失败错误信息
     */
    protected string|array $error = [];

    /**
     * 获取错误信息
     */
    public function getError(): array|string
    {
        return $this->error;
    }
    /**
     * 设置验证场景
     * @access public
     * @param string $name 场景名
     * @return $this
     */
    public function scene(string $name)
    {
        // 设置当前场景
        $this->currentScene = $name;
        return $this;
    }
    /**
     * 数据自动验证
     * @access public
     * @param array $data  数据
     * @param array $rules 验证规则
     * @return bool
     */
    public function check(array $data, array $rules = []): bool
    {
        $this->checkProperty();
        $this->error = [];

        if ($this->currentScene) {
            $this->getScene($this->currentScene);
        }
        // if (empty($this->table) || empty($this->pk)) {
        //     return true;
        // }
        if (empty($rules)) {
            // 读取验证规则
            $rules = $this->checkRule;
        }
        foreach ($this->append as $key => $rule) {
            if (!isset($rules[$key])) {
                $rules[$key] = $rule;
                unset($this->append[$key]);
            }
        }
        $currentWhere = [];
        if (!empty($rules)) {
            if (empty($this->checkWhere)) {
                throw new ModelCheckException('model验证 where 不能为空');
            }
            if (empty($this->checkMessage)) {
                throw new ModelCheckException('model验证 message 不能为空');
            }
            $currentWhere = $this->checkWhere[$this->currentScene];
        }
        $dbObj = $this->db();
        // 增加 where
        foreach ($currentWhere as $whereField => $whereSign) {
            if (!empty($data[$whereField])) {
                $dbObj->where([
                    ["{$whereField}", $whereSign, $data[$whereField]],
                ]);
            }
        }
        $onlyKeys = array_keys($this->only);
        foreach ($rules as $key => $rule) {
            // 场景检测
            if (!empty($this->only) && !in_array($key, $onlyKeys)) {
                continue;
            }
            $itemRules = $rule;
            $onlySign = $onlyKeys[$key] ?? '=';

            foreach ($itemRules as $itemKey => $itemVal) {
                if (empty($data[$key])) {
                    continue;
                }
                // 不重复
                if ($itemVal == 'repeat') {
                    // $isExist = $dbObj->where([["{$key}", $onlySign, $data[$key]]])->fetchSql()->find();
                    // dd($isExist);
                    $isExist = $dbObj->where([["{$key}", $onlySign, $data[$key]]])->field("{$key}")->find();
                    if (!empty($isExist)) {
                        $currMesage = " {$key} repeat ";
                        if (!empty($this->checkMessage[$key]['repeat'])) {
                            $currMesage = $this->checkMessage[$key]['repeat'];
                        }
                        $this->error = $currMesage;
                        return false;
                        throw new ModelCheckException($currMesage);
                    }
                }
            }
        }
        return true;
    }
    protected function checkProperty()
    {
        $className = (new \ReflectionClass($this))->getShortName();
        if (!property_exists($this, 'checkRule')) {
            throw new ModelCheckException(" {$className} 请实现 checkRule");
        }
        if (!property_exists($this, 'checkMessage')) {
            throw new ModelCheckException(" {$className} 请实现 checkMessage");
        }
        if (!property_exists($this, 'checkWhere')) {
            throw new ModelCheckException(" {$className} 请实现 checkWhere");
        }
        if (!property_exists($this, 'checkScene')) {
            throw new ModelCheckException(" {$className} 请实现 checkScene");
        }
    }
    protected function parseRules($rule)
    {
        $rules = [];
        if (is_array($rule)) {
            return $rule;
        } else if (is_string($rule)) {
            $rules[] = $rule;
        }
        return $rules;
    }
    /**
     * 获取数据验证的场景
     * @access protected
     * @param string $scene 验证场景
     * @return void
     */
    protected function getScene(string $scene): void
    {
        $this->only = $this->append = $this->remove = [];

        if (method_exists($this, 'scene' . $scene)) {
            call_user_func([$this, 'scene' . $scene]);
        } elseif (isset($this->checkScene[$scene])) {
            // 如果设置了验证适用场景
            $this->only = $this->checkScene[$scene];
        }
    }
}
