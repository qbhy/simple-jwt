<?php

namespace Qbhy\SimpleJwt;

use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\SignatureException;

/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午12:06
 */
class JWT
{
    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $payload = [];

    /** @var Encoder */
    protected static $encoder;

    /** @var Encrypter */
    protected $encrypter;

    /**
     * JWT constructor.
     *
     * @param array            $headers
     * @param array            $payload
     * @param string|Encrypter $secret
     */
    public function __construct(array $headers, array $payload, $secret)
    {
        $this->setHeaders($headers);
        $this->setPayload($payload);
        $this::setEncoder(new Base64Encoder());
        $this->setEncrypter($this::formatEncrypter($secret));

    }

    public static function formatEncrypter($secret): Encrypter
    {
        if ($secret instanceof Encrypter) {
            return $secret;
        } else {
            return new Md5Encrypter($secret);
        }
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
     * @param \Qbhy\SimpleJwt\Encrypter $encrypter
     *
     * @return static
     */
    public function setEncrypter(\Qbhy\SimpleJwt\Encrypter $encrypter): JWT
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * @return Encoder
     */
    public static function getEncoder()
    {
        if (null === static::$encoder) {
            static::$encoder = new Base64Encoder();
        }

        return static::$encoder;
    }

    /**
     * @param Encoder $encoder
     */
    public static function setEncoder(Encoder $encoder): void
    {
        static::$encoder = $encoder;
    }

    public function generateSignatureString(): string
    {
        $headersString = $this::getEncoder()->encode(json_encode($this->headers));
        $payloadString = $this::getEncoder()->encode(json_encode($this->payload));

        return "{$headersString}.{$payloadString}";
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     *
     * @return static
     */
    public function setHeaders(array $headers): JWT
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     *
     * @return static
     */
    public function setPayload(array $payload): JWT
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @param string           $token
     * @param string|Encrypter $secret
     *
     * @return static
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
     */
    public static function decryptToken(string $token, $secret)
    {
        $arr = explode('.', $token);

        if (count($arr) !== 3) {
            throw new InvalidTokenException('Invalid token');
        }

        $encrypter = static::formatEncrypter($secret);
        $encoder   = static::getEncoder();

        $signatureString = "{$arr[0]}.{$arr[1]}";

        if ($encrypter->check($signatureString, $encoder->decode($arr[2]))) {
            return new static(
                json_decode($encoder->decode($arr[0]), true),
                json_decode($encoder->decode($arr[1]), true),
                $encrypter
            );
        }

        throw new SignatureException('invalid signature');
    }

}