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

use Qbhy\SimpleJwt\Interfaces\Encrypter;

abstract class AbstractEncrypter implements Encrypter
{
    /** @var string */
    protected $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function check(string $signatureString, string $signature): bool
    {
        return $this->signature($signatureString) === $signature;
    }
}
