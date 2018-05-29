<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午2:48
 */

namespace Qbhy\SimpleJwt\Exceptions;

use Qbhy\SimpleJwt\JWT;

class TokenExpiredException extends \Exception
{
    /** @var JWT */
    protected $jwt;

    /**
     * @param JWT $jwt
     *
     * @return TokenExpiredException
     */
    public function setJwt(JWT $jwt): TokenExpiredException
    {
        $this->jwt = $jwt;

        return $this;
    }
}