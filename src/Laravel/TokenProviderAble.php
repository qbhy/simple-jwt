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
 * @package Qbhy\SimpleJwt\Laravel
 * @mixin \Qbhy\SimpleJwt\TokenProviderInterface
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait TokenProviderAble
{
    /**
     * @var array
     */
    protected static $needPayloads = [];

    /**
     * @var array
     */
    protected static $matchHeaders = [];

    /**
     * @param string $token
     *
     * @return static
     * @throws TokenProviderException
     */
    public static function fromToken(string $token)
    {
        /** @var JWT $jwt */
        $jwt = app(JWTManager::class)->fromToken($token);

        static::checkJwt($jwt);

        return static::buildModel($jwt->getPayload());
    }

    /**
     * @param JWT $jwt
     *
     * @throws TokenProviderException
     */
    protected static function checkJwt(JWT $jwt)
    {
        $headers = $jwt->getHeaders();
        foreach (static::$matchHeaders as $key => $header) {
            if (!isset($headers[$key]) || $headers[$key] !== $header) {
                throw new TokenProviderException('header invalid');
            }
        }

        $payload = $jwt->getPayload();

        $needPayloadsCount = count($needPayloads = static::$needPayloads);
        $intersectCount    = count(array_intersect($needPayloads, array_keys($payload)));

        if ($needPayloadsCount !== $intersectCount) {
            throw new TokenProviderException('payload invalid');
        }
    }

    /**
     * @param array $payload
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    abstract public static function buildModel(array $payload): Model;
}