<?php
/**
 * User: qbhy
 * Date: 2018/5/31
 * Time: 下午2:35
 */

use \Qbhy\SimpleJwt\EncryptAdapters;
use \Qbhy\SimpleJwt\Encoders;

return [

    /**
     * jwt secret
     *
     * 服务端身份标识
     */
    'secret'      => env('SIMPLE_JWT_SECRET'),

    /**
     * jwt 生命周期，单位分钟
     */
    'ttl'         => env('SIMPLE_JWT_TTL', 60),

    /**
     * 允许过期多久以内的 token 进行刷新
     */
    'refresh_ttl' => env('SIMPLE_REFRESH_TTL', 20160),

    /**
     * 加密算法支持
     */
    'algo'        => [
        'default' => env('SIMPLE_JWT_ALGO', EncryptAdapters\PasswordHashEncrypter::alg()),

        'providers' => [
            EncryptAdapters\Md5Encrypter::alg()          => EncryptAdapters\Md5Encrypter::class,
            EncryptAdapters\CryptEncrypter::alg()        => EncryptAdapters\CryptEncrypter::class,
            EncryptAdapters\PasswordHashEncrypter::alg() => EncryptAdapters\PasswordHashEncrypter::class,
            EncryptAdapters\SHA1Encrypter::alg()         => EncryptAdapters\SHA1Encrypter::class,
        ],

    ],

    /**
     * 编码类
     */
    'encoder'     => Encoders\Base64UrlSafeEncoder::class,
//    'encoder'     => Encoders\Base64Encoder::class,


];
