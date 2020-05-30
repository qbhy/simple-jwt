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

use Qbhy\SimpleJwt\Encoders\Base64UrlSafeEncoder;
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
        $manager = $this->manager();
        $this->assertTrue($this->check($manager));
        $this->assertTrue($this->check($manager->useEncrypter(Md5Encrypter::alg())));
        $this->assertTrue($this->check($manager->useEncrypter(Md5Encrypter::class)));
        $this->assertTrue($this->check($this->manager(null, new Base64UrlSafeEncoder())));
    }

    /**
     * 测试默认 md5 加密器.
     */
    public function testMd5JwtManager()
    {
        $secret = 'qbhy/simple-jwt';
        $this->assertTrue($this->check($this->manager(Md5Encrypter::class)));
        $this->assertTrue($this->check($this->manager(Md5Encrypter::class, new Base64UrlSafeEncoder())));
    }

    /**
     * 测试默认 crypt 加密器.
     */
    public function testCryptJwtManager()
    {
        $this->assertTrue($this->check($this->manager(CryptEncrypter::class)));
        $this->assertTrue($this->check($this->manager(CryptEncrypter::class, new Base64UrlSafeEncoder())));
    }

    /**
     * 测试默认 crypt 加密器.
     */
    public function testPasswordJwtManager()
    {
        $this->assertTrue($this->check($this->manager(PasswordHashEncrypter::class)));
        $this->assertTrue($this->check($this->manager(PasswordHashEncrypter::class, new Base64UrlSafeEncoder())));
    }

    /**
     * 测试默认 crypt 加密器.
     */
    public function testSHA1JwtManager()
    {
        $this->assertTrue($this->check($this->manager(SHA1Encrypter::class)));
        $this->assertTrue($this->check($this->manager(SHA1Encrypter::class, new Base64UrlSafeEncoder())));
    }

    /**
     * 测试默认黑名单功能.
     */
    public function testJwtManagerBlacklist()
    {
        $secret = 'qbhy/simple-jwt';
        $jwtManager = new JWTManager(compact('secret'));

        $jwt = $jwtManager->make(['test' => 'test']);

        $jwtManager->addBlacklist($jwt);
        try {
            $jwtManager->parse($jwt->token());
            $this->assertTrue(false, 'jwt 黑名单测试出错');
        } catch (\Throwable $exception) {
            $this->assertTrue($exception instanceof TokenBlacklistException, $exception->getMessage());
        }
    }

    protected function check(JWTManager $manager)
    {
        $jwt = $manager->make(['user_id' => 1], ['header' => 'test']);

        $token = $jwt->token();

        $manager->addBlacklist($jwt);
        $manager->removeBlacklist($jwt);

        return $manager->parse($token) instanceof JWT;
    }

    protected function manager($driver = null, $encoder = null)
    {
        return new JWTManager([
            'secret' => 'secret',
            'default' => $driver,
            'encode' => $encoder,
            'cache' => function (JWTManager $JWTManager) {
                return new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir());
            },
        ]);
    }
}
