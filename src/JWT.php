<?php

namespace Qbhy\SimpleJwt;

use Qbhy\SimpleJwt\Encoders\Base64Encoder;
use Qbhy\SimpleJwt\EncryptAdapters\CryptEncrypter;
use Qbhy\SimpleJwt\EncryptAdapters\Md5Encrypter;
use Qbhy\SimpleJwt\EncryptAdapters\SHA1Encrypter;
use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\JWTException;
use Qbhy\SimpleJwt\Exceptions\SignatureException;
use Qbhy\SimpleJwt\Interfaces\Encoder;

/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午12:06
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
     * @param array                    $headers
     * @param array                    $payload
     * @param string|AbstractEncrypter $secret
     * @param null|Encoder             $encoder
     */
    public function __construct(array $headers, array $payload, $secret, $encoder = null)
    {
        $encrypter = AbstractEncrypter::formatEncrypter($secret, Md5Encrypter::class);

        $this->setHeaders(array_merge($this->headers, ['alg' => $encrypter::alg(),], $headers))
            ->setPayload($payload)
            ->setEncoder($encoder ?? new Base64Encoder())
            ->setEncrypter($encrypter);
    }

    /**
     * @param array $supportAlgos
     */
    public static function setSupportAlgos(array $supportAlgos)
    {
        static::$supportAlgos = $supportAlgos;
    }

    /**
     * @return array
     */
    public static function getSupportAlgos(): array
    {
        return self::$supportAlgos;
    }

    /**
     * @return string
     */
    public function token(): string
    {
        $signatureString = $this->generateSignatureString();

        $signature = $this::getEncoder()->encode(
            $this->encrypter->signature($signatureString)
        );

        return "{$signatureString}.{$signature}";
    }

    /**
     * @param AbstractEncrypter $encrypter
     *
     * @return static
     */
    public function setEncrypter(AbstractEncrypter $encrypter): JWT
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * @return AbstractEncrypter
     */
    public function getEncrypter(): AbstractEncrypter
    {
        return $this->encrypter;
    }

    /**
     * @return Encoder
     */
    public function getEncoder(): Encoder
    {
        return $this->encoder;
    }

    /**
     * @param Encoder $encoder
     *
     * @return JWT
     */
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

    public static function encrypterClass(string $alg): string
    {
        return static::$supportAlgos[$alg] ?? Md5Encrypter::class;
    }

    /**
     * @param string                             $token
     * @param string|AbstractEncrypter           $secret
     * @param \Qbhy\SimpleJwt\Interfaces\Encoder $encoder
     *
     * @return static
     * @throws Exceptions\InvalidTokenException
     * @throws Exceptions\SignatureException
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
        $encoder   = $encoder ?? new Base64Encoder();

        $signatureString = "{$arr[0]}.{$arr[1]}";

        if (!is_array($headers) || !is_array($payload)) {
            throw new JWTException('bad token');
        }

        if ($encrypter->check($signatureString, $encoder->decode($arr[2]))) {
            return new static($headers, $payload, $encrypter);
        }

        throw new SignatureException('invalid signature');
    }

}