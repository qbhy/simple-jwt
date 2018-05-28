<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午4:55
 */

namespace Qbhy\SimpleJwt;


class JWTManager
{

    /** @var JWTManager[] */
    protected static $instances = [];

    /** @var int token 有效期,单位分钟 minutes */
    protected $ttl = 60;

    /** @var int token 过期多久后可以被刷新,单位分钟 minutes */
    protected $refresh_ttl = 120;

    /** @var Encrypter */
    protected $encrypter;

    /** @var Encoder */
    protected $encoder;

    /**
     * JWTManager constructor.
     *
     * @param Encrypter|string $secret
     * @param Encoder|null     $encoder
     */
    public function __construct($secret, $encoder = null)
    {
        $this->encrypter = AbstractEncrypter::formatEncrypter($secret, Md5Encrypter::class);
        $this->encoder   = $encoder ?? new Base64Encoder();

        static::$instances[$this->encrypter->getSecret()] = $this;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     *
     * @return JWTManager
     */
    public function setTtl(int $ttl): JWTManager
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @return int
     */
    public function getRefreshTtl(): int
    {
        return $this->refresh_ttl;
    }

    /**
     * @param int $refresh_ttl
     *
     * @return JWTManager
     */
    public function setRefreshTtl(int $refresh_ttl): JWTManager
    {
        $this->refresh_ttl = $refresh_ttl;

        return $this;
    }

    /**
     * @return Encrypter
     */
    public function getEncrypter(): Encrypter
    {
        return $this->encrypter;
    }

    /**
     * @param Encrypter $encrypter
     *
     * @return JWTManager
     */
    public function setEncrypter(Encrypter $encrypter): JWTManager
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * @return Encoder
     */
    public function getEncoder(): Encoder
    {
        return $this->encoder;
    }

    /**
     * @param Encoder $encoder
     *
     * @return JWTManager
     */
    public function setEncoder(Encoder $encoder): JWTManager
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * @param string|Encrypter   $secret
     * @param Base64Encoder|null $encoder
     *
     * @return JWTManager
     */
    public static function getInstance($secret, Base64Encoder $encoder = null): JWTManager
    {
        $encrypter = AbstractEncrypter::formatEncrypter($secret, Md5Encrypter::class);

        if (!isset(static::$instances[$encrypter->getSecret()])) {
            static::$instances[$encrypter->getSecret()] = new JWTManager($secret, $encoder);
        }

        return static::$instances[$encrypter->getSecret()];
    }
}