<?php

namespace shiyun\libs;

use Exception;
use shiyunUtils\base\TraitModeInstance;

/**
 * 使用 Openssl 加密解密
 * 关键在于 OPENSSL_RAW_DATA
 */
/**
 * ssl解密方式
openssl enc -d -aes-256-cbc \
    -K pwd_key_hex \
    -out xxxxx.zip \
    -in xxxxx.zip.enc \
    -iv pwd_iv_hex
 **/
class LibOpenssl
{
    use TraitModeInstance;
    /**
     * 加密方法
     */
    private string $method = 'aes-256-cbc';
    /**
     * @param string $password 密码
     * @param string $originalFilePath 原始文件路径
     * @param string $encryptedFilePath 加密后文件路径
     * @return string $iv 初始化向量IV
     */
    public function encryptFile(string $password = '', string $originalFilePath = '', string $encryptedFilePath = ''): string
    {
        $key = openssl_digest($password, 'sha256', true); // 从密码生成密钥
        $ivlen = openssl_cipher_iv_length($this->method); // 获取 初始化向量IV 的长度
        $iv = openssl_random_pseudo_bytes($ivlen); // 生成 初始化向量IV
        // 读取 ZIP 文件内容
        $originalContent = file_get_contents($originalFilePath);
        // 加密内容
        $encryptedContent = openssl_encrypt($originalContent, $this->method, $key, OPENSSL_RAW_DATA, $iv);
        if ($encryptedContent === false) {
            throw new \RuntimeException('加密失败');
        }
        // 保存加密内容
        file_put_contents($encryptedFilePath, $encryptedContent);
        // 将 IV 转为十六进制格式 输出
        $ivHex = bin2hex($iv);
        return $ivHex;
        // return base64_encode($ivHex);
    }
    /**
     * @param string $password 密码
     * @param string $encryptedFilePath 加密的文件路径
     * @param string $decryptedFilePath 解密后文件路径
     */
    public function decryptFile(string $password = '', string $ivHex = '', string $encryptedFilePath = '', string $decryptedFilePath = '')
    {
        if (empty($password) || empty($ivHex) || empty($encryptedFilePath) || empty($decryptedFilePath)) {
            throw new \InvalidArgumentException('参数不能为空');
        }

        // $ivHex = '76efc46121cf5edfbe3f5578f261e2c3'; // 假设这是加密时返回的 IV
        // 将十六进制 IV 转为二进制
        $iv = hex2bin($ivHex);
        if ($iv === false) {
            throw new \RuntimeException('IV 转换失败，可能不是有效的十六进制格式');
        }

        // 读取加密的文件内容
        $encryptedContent = file_get_contents($encryptedFilePath);
        if ($encryptedContent === false) {
            throw new \RuntimeException("无法读取文件：$encryptedFilePath");
        }
        // 从密码生成密钥
        $key = openssl_digest($password, 'sha256', true);
        if ($key === false) {
            throw new \RuntimeException('密钥生成失败');
        }
        // 解密内容
        $decryptContent = openssl_decrypt($encryptedContent, $this->method, $key, OPENSSL_RAW_DATA, $iv);
        if ($decryptContent === false) {
            throw new \RuntimeException('解密失败，请检查密码和 IV 是否正确');
        }
        // 提取初始化向量（IV）和加密后的内容
        // $ivlen = openssl_cipher_iv_length($this->method);
        // $iv = substr($encryptedContent, 0, $ivlen);
        // $encryptedContent = substr($encryptedContent, $ivlen);
        // 将解密后的内容保存到目标文件
        $result = file_put_contents($decryptedFilePath, $decryptContent);
        if ($result === false) {
            throw new \RuntimeException("无法写入解密文件：$decryptedFilePath");
        }
        return true;
    }
}
