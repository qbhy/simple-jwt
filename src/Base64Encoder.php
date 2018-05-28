<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午12:10
 */

namespace Qbhy\SimpleJwt;


class Base64Encoder implements Encoder
{
    public function encode(string $string): string
    {
        return base64_encode($string);
    }

    public function decode(string $string): string
    {
        return base64_decode($string);
    }

}