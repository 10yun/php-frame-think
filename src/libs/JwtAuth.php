<?php

namespace shiyun\libs;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

/**
 * 手册地址：https://lcobucci-jwt.readthedocs.io/en/stable/issuing-tokens/
 * https://github.com/lcobucci/jwt 【推荐】
 * https://cloud.tencent.com/developer/article/2023628
 * 
 * 去JWT的官网 https://jwt.io/#libraries-io 下载PHP的JWT包
 * 还常更新的
 * https://github.com/lindelius/php-jwt
 * https://github.com/firebase/php-jwt
 * https://github.com/tymondesigns/jwt-auth
 *
 * 【参考】：https://www.jianshu.com/p/9d7e708681a2?tdsourcetag=s_pctim_aiomsg
 * 【参考】：https://www.cnblogs.com/ruoruchujian/p/11271285.html
 */
// 上面对JWT进行了说明,接下来就是代码了
// 这里我使用的是laravel框架 在命令行里执行
//这是官方提供的代码，在你下载JWT包的时候就可以看到
// composer require lcobucci/jwt 

/**
 * 链式调用
 * 单例模式 一次请求只针对一个用户.
 * Class JwtAuth
 * @package App\Lib
 */
class JwtAuth
{

    protected $jwtContainer;
    private static $instance;

    // 加密后的token
    private $token;
    // 解析JWT得到的token
    private $decodeToken;
    // 用户ID
    private $uid;
    // jwt密钥 ,签名密钥
    private $secrect = '';

    // jwt参数
    private $_config = [
        'iss' => 'https://jwt.10yun.com', // 签发人
        'aud' => 'ctocode', // 接收人
        'id' => '3f2g57a92aa', // token的唯一标识，这里只是一个简单示例
        'expire' => 24 // 有效期,24小时
    ];

    /**
     * 单例模式 禁止该类在外部被new
     * JwtAuth constructor.
     */
    private function __construct()
    {
    }
    // public function __construct()
    // {
    // }
    /**
     * 单例模式 禁止外部克隆
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 该类的实例
     * @return JwtAuth
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function init($secrect = '')
    {
        if (empty($secrect)) {
            throw new \Exception('secrect 不存在');
        }
        $this->secrect = $secrect;
        // $key = InMemory::base64Encoded($this->secrect);
        $key = InMemory::plainText($this->secrect);
        $this->jwtContainer = Configuration::forSymmetricSigner(
            new Sha256(),
            $key
        );
        return $this;
    }
    /**
     * 获取token
     * 将token输出成字符串
     * @return string
     */
    public function getToken()
    {
        return (string) $this->token;
    }

    /**
     * 设置类内部 $token的值
     * 设置TOKEN
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
    /**
     * 设置uid
     * @param $uid
     * @return $this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * 得到 解密过后的 uid
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * 加密jwt
     * @return $this
     */
    public function encode()
    {

        // $config = $this->jwtContainer->get(Configuration::class);
        $config = $this->jwtContainer;
        assert($config instanceof Configuration);


        // 获取当前时间戳
        $now = new \DateTimeImmutable();
        $token = $config->builder()
            /* 
            * 设置签发人，设置发行人 
            * 配置颁发者（iss声明）
            */
            ->issuedBy($this->_config['iss'])
            /* 
            * 接收人：设置接收人
            * 配置听众（听证会声明）
            */
            ->permittedFor($this->_config['aud'])
            /* 
            * 配置id（jti声明），复制为头项
            */
            ->identifiedBy($this->_config['id'])
            /* 
            * 签发时间：设置生成token的时间
            * 配置令牌发出的时间（iat声明）
            */
            ->issuedAt($now)
            /* 
            * 立即生效：
            * 配置令牌可以使用的时间（nbf声明）
            */
            ->canOnlyBeUsedAfter($now)
            /* 
            * 过期时间：设置过期
            * 配置令牌的过期时间（exp claim）
            */
            ->expiresAt($now->modify("+{$this->_config['expire']} hour"))
            /* 
            * 用户id：给token设置一个ID
            * 配置一个名为“uid”的新声明 claim）
            */
            ->withClaim('uid', $this->uid)
            //
            // ->withHeader ( 'alg', 'HS256' )
            /* 
            * 签名：对上面的信息使用share256算法加密，获取token
            * 检索生成的令牌
            */
            ->getToken(
                $config->signer(),
                $config->signingKey()
            );

        $this->token = $token->toString();
        return $this;
    }

    /**
     * 解密token
     * @return \Lcobucci\JWT\Token
     */
    public function decode()
    {
        if (!$this->decodeToken) {
            $config = $this->jwtContainer;
            assert($config instanceof Configuration);

            $decodeToken = $config->parser()->parse(
                (string) $this->token
            );
            assert($decodeToken instanceof Plain);
            $this->decodeToken = $decodeToken;
            $this->uid = $decodeToken->claims()->get("uid");
        }
        return $this->decodeToken;
    }

    /**
     * 验证令牌是否有效
     * @return bool
     */
    public function validate()
    {
        $config = $this->jwtContainer;
        assert($config instanceof Configuration);

        //验证jwt id是否匹配 
        // 验证token标识
        $validate_jwt_id = new IdentifiedBy($this->_config['id']);
        //验证签发人url是否正确
        // 验证的签发人
        $validate_issued = new IssuedBy($this->_config['iss']);
        //验证客户端url是否匹配
        // 验证的接收人
        $validate_aud = new PermittedFor($this->_config['aud']);
        $config->setValidationConstraints($validate_jwt_id, $validate_issued, $validate_aud);
        $constraints = $config->validationConstraints();

        if (
            !$config->validator()->validate(
                $this->decode(),
                ...$constraints
            )
        ) {
            // throw new RuntimeException('No way!');
            return false;
        }
        return true;
    }
    /**
     * 验证令牌在生成后是否被修改
     * 验证最后一串是否一致
     * @return bool
     */
    public function verify()
    {
        $res = $this->decode()->verify(new Sha256(), $this->secrect);
        return $res;
    }
}
