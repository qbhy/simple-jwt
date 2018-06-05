<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午12:10
 */

namespace Qbhy\SimpleJwt\Encoders;


use Qbhy\SimpleJwt\Interfaces\Encoder;

class Base64UrlSafeEncoder implements Encoder
{
    public function encode(string $string): string
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }

    public function decode(string $string): string
    {
        return base64_decode(strtr($string, '-_', '+/'));
    }
}
