<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:07
 */

namespace Qbhy\SimpleJwt;


abstract class AbstractEncrypter implements Encrypter
{
    public function check(string $signatureString, $signature): bool
    {
        return $this->signature($signatureString) === $signature;
    }
}