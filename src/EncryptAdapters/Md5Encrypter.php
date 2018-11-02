<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:06
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