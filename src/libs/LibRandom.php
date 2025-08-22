<?php

namespace shiyun\libs;

/**
 * 随机串
 */
class LibRandom
{
    use TraitModeInstance;

    // 随机 字符串
    protected $chars = '';
    // 密钥 key
    protected $key = 'ctocode!@$=#=%+#com';
    // 是否 md5
    protected $isMd5 = false;

    public function setType($type = '')
    {
        switch ($type) {
            case 'str':
                $this->chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 'int':
                $this->chars = '0123456789';
                break;
            default:
                $this->chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
        }
        return $this;
    }
    public function setMd5($isBool = true)
    {
        $this->isMd5 = $isBool;
        return $this;
    }
    public function setKey($key = '')
    {
        $this->key = $key;
        return $this;
    }
    /**
     * @param int $length 生成的随机字符串的长度
     * @param array $exclude 要排除的字符数组
     * @return string 生成的随机字符串
     */
    public function getType4($length = 10, array $exclude = [])
    {
        // $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';
        $characterGroups = [
            '0123456789',               // 数字
            'abcdefghijklmnopqrstuvwxyz', // 小写字母
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ', // 大写字母
            '!@#$%^&*',                 // 特殊符号
        ];
        /**
         * 先排除
         */
        foreach ($characterGroups as $key => $group) {
            if (!empty($exclude)) {
                // 将排除的字符从字符集中移除
                foreach ($exclude as $item) {
                    $group = str_replace($item, '', $group);
                }
                $characterGroups[$key] = $group;
            }
        }
        /**
         * 然后合并
         */
        $characters = implode("", $characterGroups);
        /**
         * 随机选择至少一个字符组的字符
         * 
         */
        // 从$characters字符串中随机选择一个数字字符
        // 从$characters字符串中随机选择一个小写字母字符
        // 从$characters字符串中随机选择一个大写字母字符
        // 从$characters字符串中随机选择一个特殊符号字符
        $randomString = '';
        foreach ($characterGroups as $group) {
            $randomCharacter = $group[rand(0, strlen($group) - 1)];
            $randomString .= $randomCharacter;
        }
        // 随机选择剩下的字符，直到达到指定长度
        while (strlen($randomString) < $length) {
            $randomCharacter = $characters[rand(0, strlen($characters) - 1)];
            $randomString .= $randomCharacter;
        }
        // 将字符顺序打乱，以增加随机性
        $randomString = str_shuffle($randomString);
        return $randomString;
    }

    /**
     * 生成指定长度的随机字符串
     * @param int $len 长度
     * @param string $type
     * @return string
     */
    public function getRandom($len = 8)
    {
        if (empty($this->chars)) {
            $this->setType('str');
        }
        $chars = str_shuffle($this->chars);
        return substr($chars, 0, $len);

        $n = $this->chars;

        $vstr_a = '';
        $vstr = '';
        for ($i = 0; $i < 25; $i++) {
            $j = mt_rand(0, (strlen($n) - 1));
            $vstr_a .= $n[$j];
        }
        $vstr_b = md5($this->key . $vstr_a);
        $vstr = $vstr_b . '-' . $vstr_a;
        return $vstr;
    }

    // MD5加密截取 默认24位
    public function getMd5Str($str, $len = 24, $start = 5)
    {
        $hash = $this->key;
        return substr(md5($str . $hash), $start, $len);
    }
}
