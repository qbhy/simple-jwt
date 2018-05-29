<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午12:09
 */

namespace Qbhy\SimpleJwt\Interfaces;


interface Encoder
{
    public function encode(string $string): string;

    public function decode(string $string): string;
}