<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午4:55
 */

namespace Qbhy\SimpleJwt;


use Qbhy\SimpleJwt\Encoders\Base64Encoder;
use Qbhy\SimpleJwt\EncryptAdapters\Md5Encrypter;
use Qbhy\SimpleJwt\Exceptions\TokenExpiredException;
use Qbhy\SimpleJwt\Interfaces\Encoder;

class JWTManager
{

    /** @var JWTManager[] */
    protected static $instances = [];

    /** @var int token 有效期,单位分钟 minutes */
    protected $ttl = 60;

    /** @var int token 过期多久后可以被刷新,单位分钟 minutes */
    protected $refresh_ttl = 120;

    /** @var AbstractEncrypter */
    protected $encrypter;

    /** @var Encoder */
    protected $encoder;

    /**
     * JWTManager constructor.
     *
     * @param AbstractEncrypter|string $secret
     * @param Encoder|null             $encoder
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
     * @return AbstractEncrypter
     */
    public function getEncrypter(): AbstractEncrypter
    {
        return $this->encrypter;
    }

    /**
     * @param AbstractEncrypter $encrypter
     *
     * @return JWTManager
     */
    public function setEncrypter(AbstractEncrypter $encrypter): JWTManager
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
     * @param string|AbstractEncrypter $secret
     * @param Base64Encoder|null       $encoder
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

    public function make(array $payload, array $headers = []): JWT
    {
        $payload = array_merge($this->initPayload(), $payload);

        $jti = hash('sha256', base64_encode(json_encode($payload)) . $this->getEncrypter()->getSecret());

        $payload['jti'] = $jti;

        $jwt = new JWT($headers, $payload, $this->getEncrypter(), $this->getEncoder());

        return $jwt;
    }

    public function initPayload(): array
    {
        $timestamp = time();

        $payload = [
            'sub' => '1',
            'iss' => 'http://' . ($_SERVER['SERVER_NAME'] ?? '') . ':' . ($_SERVER["SERVER_PORT"] ?? '') . ($_SERVER["REQUEST_URI"] ?? ''),
            'exp' => $timestamp + ($this->getTtl() * 60),
            'iat' => $timestamp,
            'nbf' => $timestamp,
        ];

        return $payload;
    }

    /**
     * @param string $token
     *
     * @return \Qbhy\SimpleJwt\JWT
     * @throws \Qbhy\SimpleJwt\Exceptions\InvalidTokenException
     * @throws \Qbhy\SimpleJwt\Exceptions\SignatureException
     * @throws \Qbhy\SimpleJwt\Exceptions\TokenExpiredException
     */
    public function fromToken(string $token): JWT
    {
        $jwt = JWT::fromToken($token, $this->getEncrypter(), $this->getEncoder());

        $timestamp = time();

        $payload = $jwt->getPayload();

        if ($payload['exp'] <= $timestamp) {
            throw (new TokenExpiredException('token expired'))->setJwt($jwt);
        }

        return $jwt;
    }

}