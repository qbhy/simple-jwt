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

namespace Qbhy\SimpleJwt;

use Qbhy\SimpleJwt\Encoders\Base64Encoder;
use Qbhy\SimpleJwt\EncryptAdapters\Md5Encrypter;
use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\SignatureException;
use Qbhy\SimpleJwt\Interfaces\Encoder;

/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午12:06.
 */
class JWT
{
    /** @var array */
    protected $headers = [
        'typ' => 'jwt',
    ];

    /** @var array */
    protected $payload = [];

    /** @var Encoder */
    protected $encoder;

    /** @var AbstractEncrypter */
    protected $encrypter;

    /** @var array */
    protected static $supportAlgos = [];

    /**
     * JWT constructor.
     *
     * @param AbstractEncrypter|string $secret
     * @param null|Encoder $encoder
     */
    public function __construct(array $headers, array $payload, $secret, $encoder = null)
    {
        $encrypter = AbstractEncrypter::formatEncrypter($secret, Md5Encrypter::class);

        $this->setHeaders(array_merge($this->headers, ['alg' => $encrypter::alg()], $headers))
            ->setPayload($payload)
            ->setEncoder($encoder ?? new Base64Encoder())
            ->setEncrypter($encrypter);
    }

    public static function setSupportAlgos(array $supportAlgos)
    {
        static::$supportAlgos = $supportAlgos;
    }

    public static function getSupportAlgos(): array
    {
        return self::$supportAlgos;
    }

    public function token(): string
    {
        $signatureString = $this->generateSignatureString();

        $signature = $this::getEncoder()->encode(
            $this->encrypter->signature($signatureString)
        );

        return "{$signatureString}.{$signature}";
    }

    /**
     * @return static
     */
    public function setEncrypter(AbstractEncrypter $encrypter): JWT
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    public function getEncrypter(): AbstractEncrypter
    {
        return $this->encrypter;
    }

    public function getEncoder(): Encoder
    {
        return $this->encoder;
    }

    public function setEncoder(Encoder $encoder): JWT
    {
        $this->encoder = $encoder;

        return $this;
    }

    public function generateSignatureString(): string
    {
        $headersString = $this->getEncoder()->encode(json_encode($this->headers));
        $payloadString = $this->getEncoder()->encode(json_encode($this->payload));

        return "{$headersString}.{$payloadString}";
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return static
     */
    public function setHeaders(array $headers): JWT
    {
        $this->headers = $headers;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return static
     */
    public function setPayload(array $payload): JWT
    {
        $this->payload = $payload;

        return $this;
    }

    public static function encrypterClass(string $alg): string
    {
        return static::$supportAlgos[$alg] ?? Md5Encrypter::class;
    }

    /**
     * @param AbstractEncrypter|string $secret
     * @param \Qbhy\SimpleJwt\Interfaces\Encoder $encoder
     *
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
     * @return static
     */
    public static function fromToken(string $token, $secret, Encoder $encoder = null)
    {
        $arr = explode('.', $token);

        if (count($arr) !== 3) {
            throw new InvalidTokenException('Invalid token');
        }
        $headers = json_decode($encoder->decode($arr[0]), true);
        $payload = json_decode($encoder->decode($arr[1]), true);

        $encrypterClass = static::encrypterClass($arr[0]['alg'] ?? Md5Encrypter::alg());

        $encrypter = is_callable($secret) ? call_user_func_array($secret, [$headers, $payload]) : AbstractEncrypter::formatEncrypter($secret, $encrypterClass);
        $encoder = $encoder ?? new Base64Encoder();

        $signatureString = "{$arr[0]}.{$arr[1]}";

        if (! is_array($headers) || ! is_array($payload)) {
            throw new InvalidTokenException('Invalid token');
        }

        if ($encrypter->check($signatureString, $encoder->decode($arr[2]))) {
            return new static($headers, $payload, $encrypter);
        }

        throw new SignatureException('invalid signature');
    }
}
