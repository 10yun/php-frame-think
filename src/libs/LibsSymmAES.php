<?php

namespace shiyun\libs;

/**
 * 
 * 
 * 对称加密 - AES算法类
 * --------------------
 * 支持密钥： 64/128/256 bit（字节长度8/16/32）
 * 支持算法： DES/AES（根据密钥长度自动匹配使用：DES:64bit AES:128/256bit）
 * 支持模式： CBC/ECB/OFB/CFB
 * 密文编码： base64字符串/十六进制字符串/二进制字符串流
 * 编码方式： base64字符串/十六进制字符串/二进制字符串流(还未处理)
 * 密钥长度： 128位
 * 填充方式： PKCS5Padding（DES）
 * 补码方式： PKCS5Padding（加解密）/ NOPadding（解密）
 *
 * @author ctocode-zhw
 * @version 1.0.0
 * @date 2018/12/01
 */
class SymmAESException extends \Exception {}
class LibsSymmAES
{
    use TraitModeInstance;
    use TraitEncrypt;

    // 安全加密key
    protected $keyString = 'NjeRkITGhDvaFsRT8En2HgUaPS7fZ6Q4';
    protected $ivString = 'shiyun-iv';
    // 密钥 须是16位(默认密钥32位)
    protected $keyLast;
    // 初始化向量(偏移量)是16位 
    protected $ivLast;

    // private function addPadding($string, $blockSize = 16)
    // {
    //     $len = strlen($string);
    //     $pad = $blockSize - ($len % $blockSize);
    //     $string .= str_repeat(chr($pad), $pad);
    //     return $string;
    // }
    // private function strPadding($string)
    // {
    //     $slast = ord(substr($string, -1));
    //     $slastc = chr($slast);
    //     $pcheck = substr($string, -$slast);
    //     if (preg_match("/$slastc{" . $slast . "}/", $string)) {
    //         $string = substr($string, 0, strlen($string) - $slast);
    //         return $string;
    //     } else {
    //         return false;
    //     }
    // }


    public function setKey($keyData = '')
    {
        $this->keyString = $keyData;
        // $this->keyString = hash('sha256', $aesSett['keyString'], true);

        return $this;
    }
    public function seIv($ivData = '')
    {
        $this->ivString = $ivData;
        return $this;
    }
    public function __construct()
    {
        $this->keyLast = $this->getMd5_16($this->keyString);
        $this->ivLast = $this->getMd5_16($this->ivString);
    }
    private function getMd5_16($md5String = '')
    {
        return substr(md5($md5String), 0, 16);
    }
    /**
     * AES加密,加密字符串
     *
     * @param string $plaintext 待加密,明文字符串
     * @param string $code 加密类型，默认base64
     * @return false|string 密文
     */
    public function encrypt($plaintext, $code = 'base64')
    {
        if (!is_string($plaintext)) {
            return false;
        }
        $data = openssl_encrypt($plaintext, "AES-128-CBC", $this->keyLast, OPENSSL_RAW_DATA, $this->ivLast);
        // $data = openssl_encrypt($plaintext, 'AES-256-CBC', $this->keyLast, OPENSSL_RAW_DATA, $this->ivLast);
        $data = $this->_encode($data, $code);
        // $data = base64_encode($data);
        return $data;
    }
    /**
     * AES解密,解密字符串
     *
     * @param string $ciphertext 已加密,密文字符串
     * @param string $code 解密类型，默认base64
     * @return false|string 明文
     */
    public function decrypt($ciphertext, $code = 'base64')
    {
        if (!is_string($ciphertext)) {
            return false;
        }
        $data = $this->_decode($ciphertext, $code);
        $data = openssl_decrypt($data, "AES-128-CBC", $this->keyLast, OPENSSL_RAW_DATA, $this->ivLast);
        // $data = openssl_decrypt($data, 'AES-256-CBC', $this->keyLast, OPENSSL_RAW_DATA, $this->ivLast);
        return $data;
    }
}
