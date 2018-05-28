<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:15
 */

require 'vendor/autoload.php';

$secret = '96qbhy/simple-jwt';

$headers = [
    'ver' => 0.1,
];

$payload = [
    'user_id' => 'qbhy@gmail.com',
    'tester'  => 'qbhy',
];

$jwt = new \Qbhy\SimpleJwt\JWT($headers, $payload, $secret);

// 可以使用自己实现的 encoder 进行编码
$encoder = new \Qbhy\SimpleJwt\Base64Encoder();
$jwt->setEncoder($encoder);

// 可以使用自己实现的 encrypter 进行签名和校验
$encrypter = new \Qbhy\SimpleJwt\Md5Encrypter($secret);
$jwt->setEncrypter($encrypter);

// 生成 token
$token = $jwt->token();

print_r($token);


// 通过 token 得到 jwt 对象
$decryptedJwt = \Qbhy\SimpleJwt\JWT::decryptToken($token, $encrypter, $encoder);

// 得到 payload
$decryptedJwt->getPayload();

// 得到 headers
$decryptedJwt->getHeaders();

print_r($decryptedJwt);

$jwtManager = new \Qbhy\SimpleJwt\JWTManager($secret);

