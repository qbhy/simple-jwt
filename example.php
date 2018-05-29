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

$jwt1 = new \Qbhy\SimpleJwt\JWT($headers, $payload, $secret);

// 可以使用自己实现的 encoder 进行编码
$encoder = new \Qbhy\SimpleJwt\Encoders\Base64Encoder();
$jwt1->setEncoder($encoder);

// 可以使用自己实现的 encrypter 进行签名和校验
$encrypter = new \Qbhy\SimpleJwt\EncryptAdapters\Md5Encrypter($secret);
$jwt1->setEncrypter($encrypter);

// 生成 token
$token = $jwt1->token();

print_r($token);


// 通过 token 得到 jwt 对象
$decryptedJwt = \Qbhy\SimpleJwt\JWT::fromToken($token, $encrypter, $encoder);

// 得到 payload
$decryptedJwt->getPayload();

// 得到 headers
$decryptedJwt->getHeaders();


$jwtManager = new \Qbhy\SimpleJwt\JWTManager($secret, $encoder);

$jwt2 = $jwtManager->make($payload);

print_r($jwtManager->fromToken($jwt2->token()));
