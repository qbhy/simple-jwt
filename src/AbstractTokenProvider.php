<?php
/**
 * User: qbhy
 * Date: 2018/10/5
 * Time: 下午10:25
 */

namespace Qbhy\SimpleJwt;

use Qbhy\SimpleJwt\Exceptions\TokenProviderException;

abstract class AbstractTokenProvider implements TokenProviderInterface
{
    /** @var JWTManager */
    protected $jwtManager;

    /** @var string */
    protected $token;

    protected $needPayloads = [];

    protected $matchHeaders = [];

    /** @var JWT */
    protected $jwt;

    public function __construct(JWTManager &$jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    /**
     * @return JWT|null
     */
    public function getJwt()
    {
        return $this->jwt;
    }

    /**
     * @param bool $rebuild
     *
     * @return string
     */
    public function getToken($rebuild = true): string
    {
        if ($rebuild || is_null($this->token)) {
            $this->token = $this->buildToken();
        }

        return $this->token;
    }

    protected function buildToken(): string
    {
        $this->jwt = $this->getJwtManager()->make($this->buildPayload(), $this->matchHeaders);

        return $this->jwt->token();
    }

    abstract protected function buildPayload(): array;

    /**
     * @return JWTManager
     */
    public function getJwtManager(): JWTManager
    {
        return $this->jwtManager;
    }

    /**
     * @param string $token
     *
     * @return static
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
     * @throws Exceptions\TokenExpiredException
     * @throws TokenProviderException
     */
    public function fromToken(string $token)
    {
        $jwt = $this->getJwtManager()->fromToken($token);

        $this->checkJwt($jwt);

        $this->jwt   = $jwt;
        $this->token = $token;

        return $this;
    }

    /**
     * @param JWT $jwt
     *
     * @throws TokenProviderException
     */
    protected function checkJwt(JWT $jwt)
    {
        $headers = $jwt->getHeaders();
        foreach ($this->matchHeaders as $key => $header) {
            if (!isset($headers[$key]) || $headers[$key] !== $header) {
                throw new TokenProviderException('header invalid');
            }
        }

        $payload = $jwt->getPayload();

        $needPayloadsCount = count($needPayloads = $this->getNeedPayloads());
        $intersectCount    = count(array_intersect($needPayloads, array_keys($payload)));

        if ($needPayloadsCount !== $intersectCount) {
            throw new TokenProviderException('payload invalid');
        }
    }

    protected function getNeedPayloads(): array
    {
        return $this->needPayloads;
    }

}