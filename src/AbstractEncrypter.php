<?php
/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午1:07
 */

namespace Qbhy\SimpleJwt;


use Qbhy\SimpleJwt\Interfaces\Encrypter;

abstract class AbstractEncrypter implements Encrypter
{

    /** @var string */
    protected $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string  $signatureString
     * @param  string $signature
     *
     * @return bool
     */
    public function check(string $signatureString, string $signature): bool
    {
        return $this->signature($signatureString) === $signature;
    }

    /**
     * @param        $secret
     * @param string $defaultEncrypterClass
     *
     * @return AbstractEncrypter
     */
    public static function formatEncrypter($secret, $defaultEncrypterClass): AbstractEncrypter
    {
        if ($secret instanceof AbstractEncrypter) {
            return $secret;
        } else {
            return new $defaultEncrypterClass($secret);
        }
    }


}