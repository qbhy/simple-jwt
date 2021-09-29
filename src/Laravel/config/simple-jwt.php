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
     * 必填
     * jwt 服务端身份标识
     */
    'secret' => env('SIMPLE_JWT_SECRET'),

    /*
     * 可选配置
     * jwt 生命周期，单位秒，默认一天
     */
    'ttl' => env('SIMPLE_JWT_TTL', 60 * 60 * 24),

    /*
     * 可选配置
     * 允许过期多久以内的 token 进行刷新，默认一周
     */
    'refresh_ttl' => env('SIMPLE_JWT_REFRESH_TTL', 60 * 60 * 24 * 7),

    /*
     * 可选配置
     * 默认使用的加密类
     */
    'default' => Encrypter\PasswordHashEncrypter::class,

    /*
     * 可选配置
     * 加密类必须实现 Qbhy\SimpleJwt\Interfaces\Encrypter 接口
     */
    'drivers' => [
        Encrypter\PasswordHashEncrypter::alg() => Encrypter\PasswordHashEncrypter::class,
        Encrypter\CryptEncrypter::alg() => Encrypter\CryptEncrypter::class,
        Encrypter\SHA1Encrypter::alg() => Encrypter\SHA1Encrypter::class,
        Encrypter\Md5Encrypter::alg() => Encrypter\Md5Encrypter::class,
        Encrypter\HS256Encrypter::alg() => Encrypter\HS256Encrypter::class,
    ],

    /*
     * 可选配置
     * 编码类
     */
    'encoder' => new Encoders\Base64UrlSafeEncoder(),

    /*
     * 可选配置
     * 缓存类，用于黑名单
     */
    'cache' => new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir()),
    //    'encoder'     => Encoders\Base64Encoder::class,

    /*
     * 可选配置
     * 缓存前缀
     */
    'prefix' => env('JWT_CACHE_PREFIX', 'default'),
];
