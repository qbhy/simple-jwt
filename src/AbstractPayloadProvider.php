<?php
/**
 * User: qbhy
 * Date: 2018/10/5
 * Time: 下午10:25
 */

namespace Qbhy\SimpleJwt;

abstract class AbstractPayloadProvider implements PayloadProviderInterface
{
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * @return JWT
     */
    public function getJwt(): JWT
    {
        return $this->jwt;
    }

    /**
     * @param string     $token
     * @param JWTManager $jwtManager
     *
     * @return AbstractPayloadProvider
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
     * @throws Exceptions\TokenExpiredException
     */
    public static function fromToken(string $token, JWTManager $jwtManager)
    {
        return new static($jwtManager->fromToken($token));
    }

    public function token()
    {
        return $this->jwt->setPayload($this->getPayload())->token();
    }

}