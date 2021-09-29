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

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Qbhy\SimpleJwt\Encoders\Base64UrlSafeEncoder;
use Qbhy\SimpleJwt\EncryptAdapters\PasswordHashEncrypter;
use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\SignatureException;
use Qbhy\SimpleJwt\Exceptions\TokenBlacklistException;
use Qbhy\SimpleJwt\Exceptions\TokenExpiredException;
use Qbhy\SimpleJwt\Exceptions\TokenNotActiveException;
use Qbhy\SimpleJwt\Exceptions\TokenRefreshExpiredException;
use Qbhy\SimpleJwt\Interfaces\Encoder;
use Qbhy\SimpleJwt\Interfaces\Encrypter;

class JWTManager
{
    protected $ttl;

    /** @var int token 过期多久后可以被刷新,单位分钟 minutes */
    protected $refreshTtl;

    /** @var AbstractEncrypter */
    protected $encrypter;

    /** @var Encoder */
    protected $encoder;

    /** @var Cache */
    protected $cache;

    /**
     * @var array
     */
    protected $drivers;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * JWTManager constructor.
     */
    public function __construct(array $config)
    {
        $this->verifyConfig($config);

        $this->secret = $config['secret'];
        $this->drivers = $config['drivers'] ?? [];
        $this->prefix = $config['prefix'] ?? 'default';

        $this->resolveEncrypter($config['default'] ?? PasswordHashEncrypter::class);

        $this->encoder = $config['encoder'] ?? new Base64UrlSafeEncoder();
        $this->cache = $config['cache'] ?? new FilesystemCache(sys_get_temp_dir());
        $this->ttl = $config['ttl'] ?? 60 * 60;
        $this->refreshTtl = $config['refresh_ttl'] ?? 60 * 60 * 24 * 7; // 单位秒，默认一周内可以刷新
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function getCache(): Cache
    {
        if ($this->cache instanceof Cache) {
            return $this->cache;
        }

        return $this->cache = is_callable($this->cache) ? call_user_func_array($this->cache, [$this]) : new FilesystemCache(sys_get_temp_dir());
    }

    /**
     * 单位：分钟
     * @return $this
     */
    public function setTtl(int $ttl): JWTManager
    {
        $this->ttl = $ttl;

        return $this;
    }

    public function getRefreshTtl(): int
    {
        return $this->refreshTtl;
    }

    /**
     * 单位：分钟
     * @return $this
     */
    public function setRefreshTtl(int $ttl): JWTManager
    {
        $this->refreshTtl = $ttl;

        return $this;
    }

    public function getEncrypter(): Encrypter
    {
        return $this->encrypter;
    }

    public function getEncoder(): Encoder
    {
        return $this->encoder;
    }

    /**
     * 创建一个 jwt.
     */
    public function make(array $payload, array $headers = []): JWT
    {
        $payload = array_merge($this->initPayload(), $payload);

        $jti = hash('md5', base64_encode(json_encode([$payload, $headers])) . $this->getEncrypter()->getSecret());

        $payload['jti'] = $jti;

        return new JWT($this, $headers, $payload);
    }

    /**
     * 一些基础参数.
     */
    public function initPayload(): array
    {
        $timestamp = time();

        return [
            'sub' => '1',
            'iss' => 'http://' . ($_SERVER['SERVER_NAME'] ?? '') . ':' . ($_SERVER['SERVER_PORT'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''),
            'exp' => $timestamp + $this->getTtl(),
            'iat' => $timestamp,
            'nbf' => $timestamp,
        ];
    }

    /**
     * 解析一个jwt.
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
     * @throws Exceptions\TokenExpiredException
     */
    public function parse(string $token): JWT
    {
        $jwt = $this->justParse($token);
        $timestamp = time();
        $payload = $jwt->getPayload();

        if ($this->hasBlacklist($jwt)) {
            throw (new TokenBlacklistException('The token is already on the blacklist'))->setJwt($jwt);
        }

        if (isset($payload['exp']) && $payload['exp'] <= $timestamp) {
            throw (new TokenExpiredException('Token expired'))->setJwt($jwt);
        }

        if (isset($payload['nbf']) && $payload['nbf'] > $timestamp) {
            throw (new TokenNotActiveException('Token not active'))->setJwt($jwt);
        }

        return $jwt;
    }

    /**
     * 单纯的解析一个jwt.
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
     * @throws Exceptions\TokenExpiredException
     */
    public function justParse(string $token): JWT
    {
        $encoder = $this->getEncoder();
        $encrypter = $this->getEncrypter();
        $arr = explode('.', $token);

        if (count($arr) !== 3) {
            throw new InvalidTokenException('Invalid token');
        }

        $headers = @json_decode($encoder->decode($arr[0]), true);
        $payload = @json_decode($encoder->decode($arr[1]), true);

        $signatureString = "{$arr[0]}.{$arr[1]}";

        if (! is_array($headers) || ! is_array($payload)) {
            throw new InvalidTokenException('Invalid token');
        }

        if ($encrypter->check($signatureString, $encoder->decode($arr[2]))) {
            return new JWT($this, $headers, $payload);
        }

        throw new SignatureException('Invalid signature');
    }

    public function addBlacklist($jwt)
    {
        $now = time();
        $this->getCache()->save(
            $this->blacklistKey($jwt),
            $now,
            ($jwt instanceof JWT ? ($jwt->getPayload()['iat'] || $now) : $now) + $this->getRefreshTtl() // 存到该 token 超过 refresh 即可
        );
    }

    public function removeBlacklist($jwt)
    {
        return $this->getCache()->delete($this->blacklistKey($jwt));
    }

    public function hasBlacklist($jwt)
    {
        return $this->getCache()->contains($this->blacklistKey($jwt));
    }

    /**
     * @throws Exceptions\JWTException
     * @return JWT
     */
    public function refresh(JWT $jwt, bool $force = false)
    {
        $payload = $jwt->getPayload();

        if (! $force && isset($payload['iat'])) {
            $refreshExp = $payload['iat'] + $this->getRefreshTtl();

            if ($refreshExp <= time()) {
                throw (new TokenRefreshExpiredException('token expired, refresh is not supported'))->setJwt($jwt);
            }
        }

        unset($payload['exp'], $payload['iat'], $payload['nbf']);

        return $this->make($payload, $jwt->getHeaders());
    }

    public function useEncrypter(string $encrypter): JWTManager
    {
        $this->resolveEncrypter($encrypter);
        return $this;
    }

    /**
     * @param JWT|string $jwt
     * @return string
     */
    protected function blacklistKey($jwt)
    {
        $jti = $jwt instanceof JWT ? ($jwt->getPayload()['jti'] ?? md5($jwt->token())) : md5($jwt);

        return "jwt:blacklist:{$this->prefix}:{$jti}";
    }

    protected function verifyConfig(array $config)
    {
        if (! isset($config['secret'])) {
            throw new \InvalidArgumentException('Secret is required.');
        }
    }

    protected function resolveEncrypter($encrypter)
    {
        if ($encrypter instanceof Encrypter) {
            $this->encrypter = $encrypter;
            return;
        }
        if (class_exists($encrypter)) {
            $this->encrypter = new $encrypter($this->secret);
            return;
        }
        if (isset($this->drivers[$encrypter])) {
            $encrypter = $this->drivers[$encrypter];
            $this->encrypter = new $encrypter($this->secret);
        } else {
            $this->encrypter = new PasswordHashEncrypter($this->secret);
        }
    }
}
