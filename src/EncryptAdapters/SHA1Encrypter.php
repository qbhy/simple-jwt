<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:06
 */

namespace Qbhy\SimpleJwt\EncryptAdapters;

use Qbhy\SimpleJwt\AbstractEncrypter;

class SHA1Encrypter extends AbstractEncrypter
{
    public function signature(string $signatureString): string
    {
        return hash('sha1', $signatureString . $this->secret);
    }

    public function alg(): string
    {
        return 'sha1';
    }

}