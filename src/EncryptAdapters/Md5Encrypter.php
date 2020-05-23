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
namespace Qbhy\SimpleJwt\EncryptAdapters;

use Qbhy\SimpleJwt\AbstractEncrypter;

class Md5Encrypter extends AbstractEncrypter
{
    public function signature(string $signatureString): string
    {
        return hash('md5', $signatureString . $this->getSecret());
    }

    public static function alg(): string
    {
        return 'md5';
    }
}
