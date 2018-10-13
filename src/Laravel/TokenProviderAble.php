<?php
/**
 * User: qbhy
 * Date: 2018/10/11
 * Time: 下午11:25
 */

namespace Qbhy\SimpleJwt\Laravel;

use Illuminate\Database\Eloquent\Model;
use Qbhy\SimpleJwt\Exceptions\TokenProviderException;
use Qbhy\SimpleJwt\JWT;
use Qbhy\SimpleJwt\JWTManager;

/**
 * Trait TokenProviderAble
 *
 * @package Qbhy\SimpleJwt\Laravel
 * @mixin \Qbhy\SimpleJwt\TokenProviderInterface
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait TokenProviderAble
{
    /**
     * @param string $token
     *
     * @return Model|TokenProviderAble
     * @throws TokenProviderException
     * @throws \Qbhy\SimpleJwt\Exceptions\InvalidTokenException
     * @throws \Qbhy\SimpleJwt\Exceptions\SignatureException
     * @throws \Qbhy\SimpleJwt\Exceptions\TokenExpiredException
     */
    public static function fromToken(string $token)
    {
        /** @var JWT $jwt */
        $jwt = static::jwtManager()->fromToken($token);

        static::checkJwt($jwt);

        return static::fromPayload($jwt->getPayload());
    }

    /**
     * @param JWT $jwt
     *
     * @throws TokenProviderException
     */
    protected static function checkJwt(JWT $jwt)
    {
        $headers = $jwt->getHeaders();
        foreach (static::matchHeaders() as $key => $header) {
            if (!isset($headers[$key]) || $headers[$key] !== $header) {
                throw new TokenProviderException('header invalid');
            }
        }

        $payload = $jwt->getPayload();

        $needPayloadsCount = count($needPayloads = static::needPayloads());
        $intersectCount    = count(array_intersect($needPayloads, array_keys($payload)));

        if ($needPayloadsCount !== $intersectCount) {
            throw new TokenProviderException('payload invalid');
        }
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $jwtManager = static::jwtManager();

        return $jwtManager->make($this->buildPayload(), static::matchHeaders())->token();
    }

    /**
     * @return JWTManager
     */
    protected static function jwtManager()
    {
        return app(JWTManager::class);
    }

    /**
     * @param array $payload
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    abstract public static function fromPayload(array $payload);

    abstract protected static function matchHeaders();

    abstract protected static function needPayloads();
}