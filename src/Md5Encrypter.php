<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:06
 */

namespace Qbhy\SimpleJwt;


class Md5Encrypter extends AbstractEncrypter
{
    /** @var string */
    protected $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function signature(string $signatureString): string
    {
        return md5($signatureString . $this->secret);
    }


}