<?php

namespace shiyun\libs;

class LibEncryptArr
{
    // 加密函数(参数:数组，返回值:字符串)
    public static $key_t = "sjiofssdsfd"; //设置加密种子
    public static function setKey($str = 'shiyun')
    {
        self::$key_t = $str;
        return (new self);
    }
    public static function encrypt($cookie_array)
    {
        $txt = serialize($cookie_array);
        srand(); //生成随机数
        $encrypt_key = md5(rand(0, 10000)); //从0到10000取一个随机数
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($txt); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr] . ($txt[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode(self::key($tmp, self::$key_t));
    }

    // 解密函数(参数:字符串，返回值:数组)
    public static function decrypt($txt)
    {
        $txt = self::key(base64_decode($txt), self::$key_t);
        $tmp = '';
        for ($i = 0; $i < strlen($txt); $i++) {
            $md5 = $txt[$i];
            $tmp .= $txt[++$i] ^ $md5;
        }
        $tmp_t = unserialize($tmp);
        return $tmp_t;
    }

    protected static function key($txt, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($txt); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }
}
