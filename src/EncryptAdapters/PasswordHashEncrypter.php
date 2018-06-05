<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:06
 */

namespace Qbhy\SimpleJwt\EncryptAdapters;

use Qbhy\SimpleJwt\AbstractEncrypter;

class PasswordHashEncrypter extends AbstractEncrypter
{
    public function signature(string $signatureString): string
    {
        return password_hash($signatureString . $this->secret, PASSWORD_BCRYPT);
    }

    public function check(string $signatureString, string $signature): bool
    {
        return password_verify($signatureString . $this->secret, $signature);
    }

    public static function alg(): string
    {
        return 'bcrypt-hash';
    }

}