<?php
/**
 * User: qbhy
 * Date: 2018/10/5
 * Time: 下午10:22
 */

namespace Qbhy\SimpleJwt;

interface PayloadProviderInterface
{
    public static function fromToken(string $token, JWTManager $jwt);

    public function token();

    public function getPayload(): array;
}