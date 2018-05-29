<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午2:48
 */

namespace Qbhy\SimpleJwt\Exceptions;

use Qbhy\SimpleJwt\JWT;
use Exception;

class JWTException extends Exception
{
    /** @var JWT */
    protected $jwt;

    /**
     * @param JWT $jwt
     *
     * @return static
     */
    public function setJwt(JWT $jwt)
    {
        $this->jwt = $jwt;

        return $this;
    }

    /**
     * @return JWT
     */
    public function getJwt(): JWT
    {
        return $this->jwt;
    }
}