<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:15
 */

require 'vendor/autoload.php';

$secret = '96qbhy/simple-jwt';

$headers = [
    'alg' => 'jwt',
    'ver' => 0.1,
];

$payload = [
    'user_id' => 'qbhy@gmail.com',
    'tester'  => 'qbhy',
];

$jwt = new \Qbhy\SimpleJwt\JWT($headers, $payload, $secret);

$token = $jwt->token();

print_r($token);

$decryptedJwt = \Qbhy\SimpleJwt\JWT::decryptToken($token, $secret);

print_r($decryptedJwt);

