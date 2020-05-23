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
namespace Qbhy\SimpleJwt\Tests\Unit;

use Qbhy\SimpleJwt\Encoders\Base64Encoder;
use Qbhy\SimpleJwt\EncryptAdapters\CryptEncrypter;
use Qbhy\SimpleJwt\EncryptAdapters\Md5Encrypter;
use Qbhy\SimpleJwt\EncryptAdapters\PasswordHashEncrypter;
use Qbhy\SimpleJwt\EncryptAdapters\SHA1Encrypter;
use Qbhy\SimpleJwt\Exceptions\TokenBlacklistException;
use Qbhy\SimpleJwt\JWT;
use Qbhy\SimpleJwt\JWTManager;
use Qbhy\SimpleJwt\Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JwtTest extends TestCase
{
    /**
     * 测试默认 加密器.
     */
    public function testJwtManager()
    {
        $secret = 'qbhy/simple-jwt';
        $this->assertTrue($this->check(new JWTManager($secret)));
        $this->assertTrue($this->check(new JWTManager($secret, new Base64Encoder())));
    }

    /**
     * 测试默认 md5 加密器.
     */
    public function testMd5JwtManager()
    {
        $secret = 'qbhy/simple-jwt';
        $this->assertTrue($this->check(new JWTManager(new Md5Encrypter($secret))));
        $this->assertTrue($this->check(new JWTManager(new Md5Encrypter($secret), new Base64Encoder())));
    }

    /**
     * 测试默认 crypt 加密器.
     */
    public function testCryptJwtManager()
    {
        $secret = 'qbhy/simple-jwt';
        $this->assertTrue($this->check(new JWTManager(new CryptEncrypter($secret))));
        $this->assertTrue($this->check(new JWTManager(new CryptEncrypter($secret), new Base64Encoder())));
    }

    /**
     * 测试默认 crypt 加密器.
     */
    public function testPasswordJwtManager()
    {
        $secret = 'qbhy/simple-jwt';
        $this->assertTrue($this->check(new JWTManager(new PasswordHashEncrypter($secret))));
        $this->assertTrue($this->check(new JWTManager(new PasswordHashEncrypter($secret), new Base64Encoder())));
    }

    /**
     * 测试默认 crypt 加密器.
     */
    public function testSHA1JwtManager()
    {
        $secret = 'qbhy/simple-jwt';
        $this->assertTrue($this->check(new JWTManager(new SHA1Encrypter($secret))));
        $this->assertTrue($this->check(new JWTManager(new SHA1Encrypter($secret), new Base64Encoder())));
    }

    /**
     * 测试默认黑名单功能.
     */
    public function testJwtManagerBlacklist()
    {
        $secret = 'qbhy/simple-jwt';
        $jwtManager = new JWTManager(new SHA1Encrypter($secret));

        $jwt = $jwtManager->make(['test' => 'test']);

        $jwtManager->addBlacklist($jwt->getPayload()['jti']);
        try {
            $jwtManager->parse($jwt->token());
            $this->assertTrue(false, 'jwt 黑名单测试出错');
        } catch (\Throwable $exception) {
            $this->assertTrue($exception instanceof TokenBlacklistException, $exception->getMessage());
        }
    }

    public function check(JWTManager $manager)
    {
        $jwt = $manager->make(['user_id' => 1], ['header' => 'test']);

        $token = $jwt->token();

        return $manager->parse($token) instanceof JWT;
    }
}
