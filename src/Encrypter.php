<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:04
 */

namespace Qbhy\SimpleJwt;


interface Encrypter
{
    /**
     * @param string $signatureString
     *
     * @return string
     */
    public function signature(string $signatureString): string;

    /**
     * @param string $signatureString
     * @param        $signature
     *
     * @return bool
     */
    public function check(string $signatureString, $signature): bool;
}