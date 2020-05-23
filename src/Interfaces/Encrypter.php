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
namespace Qbhy\SimpleJwt\Interfaces;

interface Encrypter
{
    public function signature(string $signatureString): string;

    public function check(string $signatureString, string $signature): bool;

    public static function alg(): string;
}
