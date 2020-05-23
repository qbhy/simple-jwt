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
use Qbhy\SimpleJwt\Encoders;
use Qbhy\SimpleJwt\EncryptAdapters as Encrypter;

return [
    /*
     * jwt 服务端身份标识
     */
    'secret' => env('SIMPLE_JWT_SECRET'),

    /*
     * jwt 生命周期，单位分钟
     */
    'ttl' => env('SIMPLE_JWT_TTL', 60),

    /*
     * 允许过期多久以内的 token 进行刷新
     */
    'refresh_ttl' => env('SIMPLE_REFRESH_TTL', 20160),

    /*
     * 加密算法支持
     */
    'algo' => [
        'default' => env('SIMPLE_JWT_ALGO', Encrypter\PasswordHashEncrypter::alg()),

        'providers' => [
            Encrypter\Md5Encrypter::alg() => Encrypter\Md5Encrypter::class,
            Encrypter\CryptEncrypter::alg() => Encrypter\CryptEncrypter::class,
            Encrypter\PasswordHashEncrypter::alg() => Encrypter\PasswordHashEncrypter::class,
            Encrypter\SHA1Encrypter::alg() => Encrypter\SHA1Encrypter::class,
        ],
    ],

    /*
     * 编码类
     */
    'encoder' => Encoders\Base64UrlSafeEncoder::class,
    //    'encoder'     => Encoders\Base64Encoder::class,
];
