<?php

namespace shiyun\libs;

/**
 * 加密
 */
trait TraitEncrypt
{
    private function _encode($data, string $code = 'base64')
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_encode($data);
                break;
            case 'hex':
                $data = bin2hex($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }
    private function _decode($data, string $code = 'base64')
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_decode($data);
                break;
            case 'hex':
                $data = $this->_hex2bin($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    /**
     * @param string $string  密文
     * @todourl base64解码
     * @author ctocode-zwj
     */
    function urlsafe_b64decode($string)
    {
        $data = str_replace(array(
            '-',
            '_'
        ), array(
            '+',
            '/'
        ), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
    // 16进制转为2进制
    private function _hex2bin($hex = false)
    {
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }

    /**
     * @param string $string 密文
     * @return string base64编码
     */
    function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array(
            '+',
            '/',
            '='
        ), array(
            '-',
            '_',
            ''
        ), $data);
        return $data;
    }
}
