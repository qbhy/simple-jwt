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

class HS256Encrypter extends AbstractEncrypter
{
    public function signature(string $signatureString): string
    {
        return \hash_hmac('SHA256', $signatureString, $this->getSecret(), true);
    }

    public function check(string $signatureString, string $signature): bool
    {
        $hash = \hash_hmac('SHA256', $signatureString, $this->getSecret(), true);
        if (\function_exists('hash_equals')) {
            return \hash_equals($signature, $hash);
        }
        $len = \min(static::safeStrlen($signature), static::safeStrlen($hash));

        $status = 0;
        for ($i = 0; $i < $len; ++$i) {
            $status |= (\ord($signature[$i]) ^ \ord($hash[$i]));
        }
        $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));

        return $status === 0;
    }

    public static function alg(): string
    {
        return 'HS256';
    }
}
