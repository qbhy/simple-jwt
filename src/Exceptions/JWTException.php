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
namespace Qbhy\SimpleJwt\Exceptions;

use Exception;
use Qbhy\SimpleJwt\JWT;

class JWTException extends Exception
{
    /** @var JWT */
    protected $jwt;

    /**
     * @return static
     */
    public function setJwt(JWT $jwt)
    {
        $this->jwt = $jwt;

        return $this;
    }

    public function getJwt(): JWT
    {
        return $this->jwt;
    }
}
