<?php

declare(strict_types=1);
/**
 * This file is part of qbhy/simple-jwt.
 *
 * @link     https://github.com/qbhy/simple-jwt
 * @document https://github.com/qbhy/simple-jwt/blob/master/README.md
 * @contact  qbhy0715@qq.com
 * @license  https://github.com/qbhy/simple-jwt/blob/master/LICENSE
 */

require 'vendor/autoload.php';

use Qbhy\SimpleJwt\Encoders;
use Qbhy\SimpleJwt\EncryptAdapters;
use Qbhy\SimpleJwt\Exceptions;
use Qbhy\SimpleJwt\JWT;
use Qbhy\SimpleJwt\JWTManager;

$secret = '96qbhy/simple-jwt';

$headers = [
    'ver' => 0.1,
    't' => 'user',
];

$payload = [
    'user_id' => 'qbhy@gmail.com',
    'tester' => 'qbhy',
];

// 可以使用自己实现的 encrypter 进行签名和校验，请继承自 AbstractEncrypter 抽象类
$encrypter = new EncryptAdapters\PasswordHashEncrypter($secret);

// 可以使用自己实现的 encoder 进行编码，请实现 Encoder 接口
$encoder = new Encoders\Base64Encoder();

// 实例化 jwt manager
$jwtManager = new JWTManager($secret, $encoder);

// 设置 token 有效时间，单位 分钟
$jwtManager->setTtl(60);
// 设置 token 过期后多久时间内允许刷新，单位 分钟
$jwtManager->setRefreshTtl(120);

// 通过 jwt manager 示例化 jwt ，标准 jwt
$jwt0 = $jwtManager->make($payload, $headers);

class UserToken extends \Qbhy\SimpleJwt\AbstractTokenProvider
{
    protected $needPayloads = [
        'user_id',
    ];

    protected $matchHeaders = [
        't' => 'user',
    ];

    protected $user;

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * 返回 user 模型.
     */
    public function user()
    {
        return $this->user;
//        return User::query()->findOrFail($this->getJwt()->getPayload()['user_id']);
    }

    protected function buildPayload(): array
    {
        return [
            'user_id' => $this->user,
        ];
    }
}

// 生成 token，当然你也可以使用链式调用，例如:  $jwtManager->make($payload, $headers)->token()
$token = $jwt0->token();
print_r($token);

$userTokenProvider = new UserToken($jwtManager);

print_r(PHP_EOL . $userTokenProvider->fromToken($token)->user() . PHP_EOL);

var_dump($userTokenProvider->setUser('new user')->getToken());

try {
    // 通过 token 得到 jwt 对象
    $jwt1 = $jwtManager->fromToken($token);
} catch (Exceptions\TokenExpiredException $tokenExpiredException) {
    // 如果已经过期了，也可以尝试刷新此 jwt ,第二个参数如果为 true 将忽略 refresh ttl 检查
    $jwt1 = $jwtManager->refresh($tokenExpiredException->getJwt(), true);
}

// 得到 payload
$jwt1->getPayload();

// 得到 headers
$jwt1->getHeaders();

print_r($jwt1);

// 自己实例化 jwt ，完全纯净的 jwt ，无多余 payload
$jwt2 = new JWT($headers, $payload, $secret);

// 仍然可以自定义 encoder 和 encrypter
$jwt2->setEncoder($encoder);
$jwt2->setEncrypter($encrypter);

print_r($jwtManager->make(['group_id' => 1, 'invite_user' => 'A'])->token());
