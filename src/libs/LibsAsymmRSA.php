<?php

namespace shiyun\libs;

/**
 * 
 * 非对称加密 - RSA算法类
 * 签名编码： base64字符串/十六进制字符串/二进制字符串流
 * 密文编码： base64字符串/十六进制字符串/二进制字符串流
 * 填充方式： PKCS1Padding（加解密）/NOPadding（解密）
 *
 * Notice:Only accepts a single block. Block size is equal to the RSA key size!
 * 如密钥长度为1024 bit，则加密时数据需小于128字节，加上PKCS1Padding本身的11字节信息，所以明文需小于117字节
 *
 * @author: ctocode-zwj
 * @version: 1.0.0
 * @date: 2018/10/31
 */
class AsymmRSAException extends \Exception {}
class LibsAsymmRSA
{
    use TraitModeInstance;
    use TraitEncrypt;
    /**
     * 私钥
     */
    protected string $privateKey = ''; //null
    /**
     * 公钥
     */
    protected string $publicKey = ''; //null
    /**
     * 构造函数
     * @param string 公钥文件（验签和加密时传入）
     * @param string 私钥文件（签名和解密时传入）
     */
    public function __construct2($public_key_file = '', $private_key_file = '')
    {
        if ($public_key_file) {
            $this->_getPublicKey($public_key_file);
        }
        if ($private_key_file) {
            $this->_getPrivateKey($private_key_file);
        }
    }
    /**
     * @desc 初始化公私钥
     * @param $privateKey
     * @param $publicKey
     */
    public function __construct(string $privateKey = '', string $publicKey = '')
    {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }
    /**
     * @desc 设置私钥
     * @param $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }
    /**
     * @desc 设置私钥
     * @param $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }
    /**
     * 获取私钥
     * @return bool|resource
     */
    public function getPrivateKey()
    {
        return openssl_pkey_get_private($this->privateKey);
    }
    private function _getPrivateKey($file)
    {
        $key_content = $this->_readFile($file);
        if ($key_content) {
            $this->privateKey = openssl_get_privatekey($key_content);
        }
    }
    /**
     * 获取公钥
     * @return bool|resource
     */
    public function getPublicKey()
    {
        return openssl_pkey_get_public($this->publicKey);
    }

    private function _getPublicKey($file)
    {
        $key_content = $this->_readFile($file);
        if ($key_content) {
            $this->publicKey = openssl_get_publickey($key_content);
        }
    }

    /**
     * 检测填充类型
     * 加密只支持PKCS1_PADDING
     * 解密支持PKCS1_PADDING和NO_PADDING
     *
     * @param int 填充模式
     * @param string 加密en/解密de
     * @return bool
     */
    private function _checkPadding($padding, $type)
    {
        if ($type == 'en') {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        } else {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                case OPENSSL_NO_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    private function _readFile($file)
    {
        $ret = false;
        if (!file_exists($file)) {
            throw new AsymmRSAException("The file {$file} is not exists");
        } else {
            $ret = file_get_contents($file);
        }
        return $ret;
    }

    /**
     * 生成签名,创建签名
     *
     * @param string $data 签名材料
     * @param string $code 签名编码（base64/hex/bin）
     * @return bool|string 签名值
     */
    public function createSign(string $data, string $code = 'base64')
    {
        if (!is_string($data)) {
            // throw new AsymmRSAException('createSign 签名错误');
            return false;
        }
        $ret = false;
        if (openssl_sign($data, $ret, $this->privateKey)) {
            $ret = $this->_encode($ret, $code);
        }
        return $ret;
        return openssl_sign($data, $sign, $this->getPrivateKey(), OPENSSL_ALGO_SHA256) ? base64_encode($sign) : false;
    }
    /**
     * 验证签名
     *
     * @param string $data 签名材料
     * @param string $sign 签名值
     * @param string $code 签名编码（base64/hex/bin）
     * @return bool
     */
    public function verifySign(string $data, string $sign, string $code = 'base64')
    {
        // if (!is_string($data) || !is_string($sign)) {
        //     return false;
        // }
        $sign = $this->_decode($sign, $code);
        // return (bool) openssl_verify($data, $sign, $this->getPublicKey(), OPENSSL_ALGO_SHA256);
        $ret = false;
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $this->publicKey)) {
                case 1:
                    $ret = true;
                    break;
                case 0:
                case -1:
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    /**
     * 加密
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
    {
        if (!is_string($data)) {
            return false;
        }

        $ret = false;
        if (!$this->_checkPadding($padding, 'en'))
            throw new AsymmRSAException("padding error");

        if (openssl_public_encrypt($data, $result, $this->publicKey, $padding)) {
            $ret = $this->_encode($result, $code);
        }
        return $ret;
    }

    /**
     * 解密
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false)
    {
        if (!is_string($data)) {
            return false;
        }
        $ret = false;
        $data = $this->_decode($data, $code);
        if (!$this->_checkPadding($padding, 'de'))
            throw new AsymmRSAException("padding error");

        if ($data !== false) {
            if (openssl_private_decrypt($data, $result, $this->privateKey, $padding)) {
                $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
            }
        }
        return $ret;
    }

    /**
     * 私钥加密
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function privateEncrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
    {
        if (!is_string($data)) {
            return false;
        }

        if (!$this->_checkPadding($padding, 'en'))
            throw new AsymmRSAException("padding error");

        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $this->privateKey);
            $crypto .= $encryptData;
        }
        $encrypted = $this->_encode($crypto, $code); // 加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    /**
     * 私钥解密
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function privateDecrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
    {
        if (!is_string($data)) {
            return false;
        }
        $ret = false;
        $data = $this->_decode($data, $code);
        if (!$this->_checkPadding($padding, 'de'))
            throw new AsymmRSAException("padding error");

        if ($data !== false) {
            $crypto = '';
            foreach (str_split($data, 128) as $chunk) {
                openssl_private_decrypt($chunk, $decryptData, $this->privateKey, $padding);
                $crypto .= $decryptData;
            }
            return $crypto;
        }
    }

    /**
     * 公钥加密
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function publicEncrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
    {
        if (!is_string($data)) {
            return false;
        }

        if (!$this->_checkPadding($padding, 'en'))
            throw new AsymmRSAException("padding error");

        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->publicKey);
            $crypto .= $encryptData;
        }
        $encrypted = $this->_encode($crypto, $code); // 加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    /**
     * 公钥解密
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function publicDecrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
    {
        if (!is_string($data)) {
            return false;
        }
        $ret = false;
        $data = $this->_decode($data, $code);
        if (!$this->_checkPadding($padding, 'de'))
            throw new AsymmRSAException("padding error");

        if ($data !== false) {
            $crypto = '';
            foreach (str_split($data, 128) as $chunk) {
                openssl_public_decrypt($chunk, $decryptData, $this->publicKey, $padding);
                $crypto .= $decryptData;
            }
            return $crypto;
        }
    }
    /**
     * @desc 生成RSA2文件
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function generateRsa($userId = 0)
    {
        $runtime = syPathRuntime();
        $pemPath = $runtime . 'pem/' . date('Ym/');
        !is_dir($pemPath) && mkdir($pemPath, 0755, true);

        // 生成原始 RSA私钥文件 PKCS1(非JAVA适用)
        $pkcs1PrivateFileName = $pemPath . 'rsa_private_key_pkcs1_' . $userId . '.pem';
        system("openssl genrsa -out {$pkcs1PrivateFileName} 1024");

        // 将原始 RSA私钥转换为 pkcs8格式 PKCS8(JAVA适用)
        $pkcs8PrivateFileName = $pemPath . 'rsa_private_key_pkcs8_' . $userId . '.pem';
        system("openssl pkcs8 -topk8 -inform PEM -in {$pkcs1PrivateFileName} -outform PEM -nocrypt -out {$pkcs8PrivateFileName}");

        // 生成RSA公钥
        $rsaPublicFileName = $pemPath . 'rsa_public_key_pkcs8_' . $userId . '.pem';
        system("openssl rsa -in {$pkcs1PrivateFileName} -pubout -out {$rsaPublicFileName}");

        $pkcs1PrivateContent = file_get_contents($pkcs1PrivateFileName);
        $pkcs8PrivateContent = file_get_contents($pkcs8PrivateFileName);
        $rsaPublicContent    = file_get_contents($rsaPublicFileName);

        @unlink($pkcs1PrivateFileName);
        @unlink($pkcs8PrivateFileName);
        @unlink($rsaPublicFileName);

        return [
            'pkcs1PrivateContent' => $pkcs1PrivateContent,
            'pkcs8PrivateContent' => $pkcs8PrivateContent,
            'rsaPublicContent'    => $rsaPublicContent
        ];
    }


    /**
     * 创建签名
     * @param string $data 数据
     * @return bool|string
     */
    public function createSignJava($data = '')
    {
        if (!is_string($data)) {
            return false;
        }
        return openssl_sign($data, $sign, $this->getPrivateKey(), OPENSSL_ALGO_DSS1) ? base64_encode($sign) : false;
    }

    /**
     * 验证签名
     * @param string $data 数据
     * @param string $sign 签名
     * @return bool
     */
    public function verifySignJava($data = '', $sign = '')
    {
        if (!is_string($sign) || !is_string($sign)) {
            return false;
        }
        return (bool) openssl_verify(
            $data,
            base64_decode($sign),
            $this->getPublicKey(),
            OPENSSL_ALGO_DSS1
        );
    }

    /**
     * @desc 将字符串格式公私钥格式化为pem格式公私钥
     * @param string $secretKey 公钥和私钥
     * @param string $type 公私钥
     * @return string
     */
    public  function formatSecretKey($secretKey, $type = 'private')
    {
        // 64个英文字符后接换行符"\n",最后再接换行符"\n"
        $key = (wordwrap($secretKey, 64, "\n", true)) . "\n";
        // 添加pem格式头和尾
        if ($type == 'public') {
            $pem_key = "-----BEGIN PUBLIC KEY-----\n" . $key . "-----END PUBLIC KEY-----\n";
        } else if ($type == 'private') {
            $pem_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $key . "-----END RSA PRIVATE KEY-----\n";
        } else {
            return false;
        }
        return $pem_key;
    }
    /**
     * @desc 将JAVA密钥替换为非JAVA适用头部和尾部
     * @param string $privateKey
     * @return string
     */
    public function replacePrivateKey($privateKey)
    {
        $beforeReplace = ['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----'];
        $afterReplace = ['-----BEGIN RSA PRIVATE KEY-----', '-----END RSA PRIVATE KEY-----'];
        return str_replace($beforeReplace, $afterReplace, $privateKey);
    }
}
