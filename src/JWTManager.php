<?php

declare(strict_types=1);
/**
 * This file is part of qbhy/simple-jwt.
 *
 * @link     https://github.com/qbhy/simple-jwt
 * @document https://github.com/qbhy/simple-jwt/blob/master/README.md
 * @contact  qbhy0715@qq.com
 * @license  https://github.com/qbhy/simple-jwt/blob/master/LICENSE
 */

namespace Qbhy\SimpleJwt;

use Qbhy\SimpleJwt\Encoders\Base64Encoder;
use Qbhy\SimpleJwt\EncryptAdapters\Md5Encrypter;
use Qbhy\SimpleJwt\Exceptions\TokenExpiredException;
use Qbhy\SimpleJwt\Exceptions\TokenRefreshExpiredException;
use Qbhy\SimpleJwt\Interfaces\Encoder;

class JWTManager
{
    /** @var JWTManager[] */
    protected static $instances = [];

    /** @var int token 有效期,单位分钟 minutes */
    protected $ttl = 60 * 60;

    /** @var int token 过期多久后可以被刷新,单位分钟 minutes */
    protected $refresh_ttl = 120 * 60;

    /** @var AbstractEncrypter */
    protected $encrypter;

    /** @var Encoder */
    protected $encoder;

    /**
     * JWTManager constructor.
     *
     * @param AbstractEncrypter|string $secret
     * @param null|Encoder $encoder
     */
    public function __construct($secret, $encoder = null)
    {
        $this->encrypter = AbstractEncrypter::formatEncrypter($secret, Md5Encrypter::class);
        $this->encoder = $encoder ?? new Base64Encoder();

        static::$instances[$this->encrypter->getSecret()] = $this;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function setTtl(int $ttl): JWTManager
    {
        $this->ttl = $ttl * 60;

        return $this;
    }

    public function getRefreshTtl(): int
    {
        return $this->refresh_ttl;
    }

    public function setRefreshTtl(int $refresh_ttl): JWTManager
    {
        $this->refresh_ttl = $refresh_ttl * 60;

        return $this;
    }

    public function getEncrypter(): AbstractEncrypter
    {
        return $this->encrypter;
    }

    public function setEncrypter(AbstractEncrypter $encrypter): JWTManager
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    public function getEncoder(): Encoder
    {
        return $this->encoder;
    }

    public function setEncoder(Encoder $encoder): JWTManager
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * @param AbstractEncrypter|string $secret
     */
    public static function getInstance($secret, Base64Encoder $encoder = null): JWTManager
    {
        $encrypter = AbstractEncrypter::formatEncrypter($secret, Md5Encrypter::class);

        if (! isset(static::$instances[$encrypter->getSecret()])) {
            static::$instances[$encrypter->getSecret()] = new JWTManager($secret, $encoder);
        }

        return static::$instances[$encrypter->getSecret()];
    }

    public function make(array $payload, array $headers = []): JWT
    {
        $payload = array_merge($this->initPayload(), $payload);

        $jti = hash('md5', base64_encode(json_encode($payload)) . $this->getEncrypter()->getSecret());

        $payload['jti'] = $jti;

        return new JWT($headers, $payload, $this->getEncrypter(), $this->getEncoder());
    }

    public function initPayload(): array
    {
        $timestamp = time();

        $payload = [
            'sub' => '1',
            'iss' => 'http://' . ($_SERVER['SERVER_NAME'] ?? '') . ':' . ($_SERVER['SERVER_PORT'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''),
            'exp' => $timestamp + $this->getTtl(),
            'iat' => $timestamp,
            'nbf' => $timestamp,
        ];

        return $payload;
    }

    /**
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
     * @throws Exceptions\TokenExpiredException
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

    public function refresh(JWT $jwt, bool $force = false)
    {
        $payload = $jwt->getPayload();

        if (! $force) {
            $refreshExp = $payload['exp'] + $this->getRefreshTtl();

            if ($refreshExp <= time()) {
                throw (new TokenRefreshExpiredException('token expired, refresh is not supported'))->setJwt($jwt);
            }
        }

        unset($payload['exp'], $payload['iat'], $payload['nbf']);

        return $this->make($payload, $jwt->getHeaders());
    }
}
