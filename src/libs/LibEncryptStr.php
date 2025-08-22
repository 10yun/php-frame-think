<?php

namespace shiyun\libs;

class LibEncryptStr
{
    private static $secretKey = 'ctocode';

    public static function strEncode($value)
    {
        if (self::verifySignature($value)) {
            return $value;
        }
        $encrypted = LibEncryptNum::numberEncode($value);
        $signature = self::getSign($encrypted);
        return $signature . '|' . $encrypted;
    }

    public static function strDecode($value)
    {
        if (!self::verifySignature($value)) {
            return $value;
        }
        list($signature, $encrypted) = explode('|', $value, 2);
        return LibEncryptNum::numberDecode($encrypted);;
    }
    private static function verifySignature($value)
    {
        if (!str_contains($value, '|')) {
            return false;
        }
        list($signature, $encrypted) = explode('|', $value, 2);
        $expected = self::getSign($encrypted);
        return hash_equals($signature, $expected);
    }
    protected static function getSign($encrypted)
    {
        return hash_hmac('sha256', $encrypted, self::$secretKey);
    }
}
